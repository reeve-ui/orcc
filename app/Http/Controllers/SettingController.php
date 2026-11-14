<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChurchSettingRequest;
use App\Http\Requests\OrccdataImportRequest;
use App\Http\Requests\ResetDataRequest;
use App\Models\ChurchSetting;
use App\Models\Family;
use App\Models\Member;
use App\Services\DataResetService;
use App\Services\LogoService;
use App\Services\OrccdataImporter;
use App\Services\OrccdataTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class SettingController extends Controller
{
    public function edit(Request $request): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        return view('settings.edit', [
            'settings' => ChurchSetting::current(),
            'familyCount' => Family::query()->count(),
            'memberCount' => Member::query()->count(),
        ]);
    }

    public function update(ChurchSettingRequest $request, LogoService $logos): RedirectResponse
    {
        $settings = ChurchSetting::current();
        $data = $request->validated();
        $oldLogoPath = $settings->logo_path;
        $newLogoPath = null;
        $removeLogo = false;

        unset($data['logo'], $data['remove_logo']);

        if ($request->hasFile('logo')) {
            try {
                $newLogoPath = $logos->store($request->file('logo'));
            } catch (ValidationException $exception) {
                throw $exception;
            } catch (Throwable $exception) {
                report($exception);

                return back()
                    ->withErrors(['logo' => 'Logo gagal diproses. Silakan coba lagi.'])
                    ->withInput();
            }

            $data['logo_path'] = $newLogoPath;
        } elseif ($request->boolean('remove_logo') && $oldLogoPath) {
            $data['logo_path'] = null;
            $removeLogo = true;
        }

        try {
            $settings->update($data);
        } catch (Throwable $exception) {
            if ($newLogoPath) {
                Storage::disk('public')->delete($newLogoPath);
            }

            throw $exception;
        }

        if ($oldLogoPath && ($newLogoPath || $removeLogo)) {
            Storage::disk('public')->delete($oldLogoPath);
        }

        return redirect()
            ->route('settings.edit')
            ->with('status', 'Pengaturan gereja berhasil disimpan.');
    }

    public function resetData(ResetDataRequest $request, DataResetService $reset): RedirectResponse
    {
        try {
            $result = $reset->reset();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('settings.edit')
                ->with('error', 'Reset data gagal. Tidak ada perubahan yang disimpan.');
        }

        Log::warning('Admin mereset seluruh data KKJ dan jemaat.', [
            'user_id' => $request->user()?->id,
            'families' => $result['families'],
            'members' => $result['members'],
            'photos' => $result['photos'],
        ]);

        return redirect()
            ->route('settings.edit')
            ->with('status', "Reset data selesai. {$result['families']} KKJ dan {$result['members']} jemaat dihapus.");
    }

    public function import(OrccdataImportRequest $request, OrccdataImporter $importer): RedirectResponse
    {
        try {
            $result = $importer->import(
                (string) $request->file('excel')->getRealPath(),
                $request->boolean('fresh'),
                $request->boolean('dry_run')
            );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('settings.edit')
                ->with('error', 'Impor gagal: '.$exception->getMessage());
        }

        $status = $result['dry_run']
            ? 'Uji coba impor selesai. Tidak ada perubahan yang disimpan.'
            : 'Impor data Excel selesai.';

        return redirect()
            ->route('settings.edit')
            ->with('status', $status)
            ->with('import_result', $result);
    }

    public function template(Request $request, OrccdataTemplate $template): StreamedResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $spreadsheet = $template->make();

        return response()->streamDownload(function () use ($spreadsheet): void {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'template-impor-orccdata.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}

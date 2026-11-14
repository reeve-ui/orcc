<?php

namespace App\Http\Controllers;

use App\Models\ChurchSetting;
use App\Models\Family;
use App\Models\Member;
use App\Services\QrCodeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class FamilyCardController extends Controller
{
    public function show(Family $family, QrCodeService $qrCode): Response
    {
        return $this->render($family, $qrCode, false);
    }

    public function download(Family $family, QrCodeService $qrCode): Response
    {
        return $this->render($family, $qrCode, true);
    }

    private function render(Family $family, QrCodeService $qrCode, bool $download): Response
    {
        $this->authorize('view', $family);

        $family->load('members');
        $family->ensureVerificationToken();

        $members = $family->members
            ->sortBy(fn (Member $member) => sprintf(
                '%02d-%02d-%s',
                $member->status_rank,
                $member->child_order ?? 99,
                $member->full_name
            ))
            ->values();

        $settings = ChurchSetting::current();
        $settings->applyToConfig();

        $pdf = Pdf::loadView('pdf.family-card', [
            'family' => $family,
            'members' => $members,
            'qrMatrix' => $qrCode->matrix($family->verification_url ?? $family->kkj_id),
            'church' => config('church'),
            'logoDataUri' => $settings->logoDataUri(),
        ])->setPaper(config('church.card.paper', 'a4'), 'landscape');

        $filename = 'KKJ-'.$family->kkj_id.'.pdf';

        return $download
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }
}

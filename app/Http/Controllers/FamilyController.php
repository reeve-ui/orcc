<?php

namespace App\Http\Controllers;

use App\Http\Requests\FamilyRequest;
use App\Models\Family;
use App\Models\Member;
use App\Models\Region;
use App\Services\KkjNumberGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FamilyController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Family::class);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
        ]);

        $families = Family::query()
            ->withCount('members')
            ->search($filters['q'] ?? null)
            ->when($filters['district'] ?? null, fn ($query, string $district) => $query->where('district', $district))
            ->orderBy('head_name')
            ->paginate(15)
            ->withQueryString();

        $districts = Family::query()
            ->whereNotNull('district')
            ->where('district', '<>', '')
            ->distinct()
            ->orderBy('district')
            ->pluck('district');

        return view('families.index', [
            'families' => $families,
            'districts' => $districts,
            'filters' => $filters,
        ]);
    }

    public function create(): View|RedirectResponse
    {
        $this->authorize('create', Family::class);

        if ($blocked = $this->setupBlocker()) {
            return $blocked;
        }

        return view('families.form', [
            'family' => new Family,
            'regionsReady' => true,
            'kkjPreview' => app(KkjNumberGenerator::class)->preview(),
        ]);
    }

    public function store(FamilyRequest $request): RedirectResponse
    {
        $this->authorize('create', Family::class);

        if ($blocked = $this->setupBlocker()) {
            return $blocked;
        }

        $data = $request->validated();
        unset($data['photo']);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('family-photos', 'public');
        }

        $family = Family::create($data);

        return redirect()
            ->route('families.show', $family)
            ->with('status', 'Data KKJ berhasil ditambahkan.');
    }

    private function setupBlocker(): ?RedirectResponse
    {
        $code = strtoupper(trim((string) config('church.code')));

        if (! preg_match('/^[A-Z0-9]{5}$/', $code)) {
            return redirect()
                ->route('settings.edit')
                ->with('warning', 'Atur kode gereja 5 karakter terlebih dahulu sebelum menambah KKJ.');
        }

        if (! Region::query()->exists()) {
            return redirect()
                ->route('families.index')
                ->with('error', 'Data wilayah belum tersedia. Jalankan perintah php artisan regions:import terlebih dahulu.');
        }

        return null;
    }

    public function show(Family $family): View
    {
        $this->authorize('view', $family);

        $family->ensureVerificationToken();
        $family->load('members');

        $members = $family->members
            ->sortBy(fn (Member $member) => sprintf(
                '%02d-%02d-%s',
                $member->status_rank,
                $member->child_order ?? 99,
                $member->full_name
            ))
            ->values();

        return view('families.show', [
            'family' => $family,
            'members' => $members,
        ]);
    }

    public function edit(Family $family): View
    {
        $this->authorize('update', $family);

        return view('families.form', [
            'family' => $family,
            'regionsReady' => Region::query()->exists(),
            'kkjPreview' => app(KkjNumberGenerator::class)->preview(),
        ]);
    }

    public function update(FamilyRequest $request, Family $family): RedirectResponse
    {
        $this->authorize('update', $family);

        $data = $request->validated();
        unset($data['photo']);

        if ($request->hasFile('photo')) {
            if ($family->photo_path) {
                Storage::disk('public')->delete($family->photo_path);
            }

            $data['photo_path'] = $request->file('photo')->store('family-photos', 'public');
        }

        $family->update($data);

        return redirect()
            ->route('families.show', $family)
            ->with('status', 'Data KKJ berhasil diperbarui.');
    }

    public function destroy(Family $family): RedirectResponse
    {
        $this->authorize('delete', $family);

        if ($family->photo_path) {
            Storage::disk('public')->delete($family->photo_path);
        }

        $headName = $family->head_name;
        $family->delete();

        return redirect()
            ->route('families.index')
            ->with('status', "Data KKJ {$headName} berhasil dihapus.");
    }

    public function mine(Request $request): RedirectResponse
    {
        $member = $request->user()->member;

        if (! $member || ! $member->family) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Akun Anda belum ditautkan ke data keluarga. Silakan hubungi sekretariat gereja.');
        }

        return redirect()->route('families.show', $member->family_id);
    }
}

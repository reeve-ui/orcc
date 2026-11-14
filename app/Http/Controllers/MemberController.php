<?php

namespace App\Http\Controllers;

use App\Enums\Gender;
use App\Http\Requests\MemberRequest;
use App\Models\Family;
use App\Models\Member;
use App\Services\KtjNumberGenerator;
use App\Support\AgeCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Member::class);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', Rule::in(Member::FAMILY_STATUSES)],
            'gender' => ['nullable', 'string', Rule::in(array_keys(Gender::options()))],
            'age' => ['nullable', 'string', Rule::in(AgeCategory::labels())],
        ]);

        $members = Member::query()
            ->with('family')
            ->search($filters['q'] ?? null)
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('family_status', $status))
            ->when($filters['gender'] ?? null, fn ($query, string $gender) => $query->where('gender', $gender))
            ->when($filters['age'] ?? null, fn ($query, string $age) => $query->ageCategory($age))
            ->orderBy('full_name')
            ->paginate(20)
            ->withQueryString();

        return view('members.index', [
            'members' => $members,
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Member::class);

        $familyId = $request->integer('family_id');

        return view('members.form', [
            'member' => new Member(['family_id' => $familyId ?: null]),
            'families' => $this->families(),
        ]);
    }

    public function store(MemberRequest $request, KtjNumberGenerator $ktjNumbers): RedirectResponse
    {
        $this->authorize('create', Member::class);

        $data = $this->memberData($request, $ktjNumbers);
        unset($data['photo']);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('member-photos', 'public');
        }

        $member = Member::create($data);

        return redirect()
            ->route('families.show', $member->family_id)
            ->with('status', "Data {$member->full_name} berhasil ditambahkan.");
    }

    public function show(Member $member): View
    {
        $this->authorize('view', $member);

        $member->load('family');

        return view('members.show', [
            'member' => $member,
        ]);
    }

    public function edit(Member $member): View
    {
        $this->authorize('update', $member);

        return view('members.form', [
            'member' => $member,
            'families' => $this->families(),
        ]);
    }

    public function update(MemberRequest $request, Member $member, KtjNumberGenerator $ktjNumbers): RedirectResponse
    {
        $this->authorize('update', $member);

        $data = $this->memberData($request, $ktjNumbers, $member);
        unset($data['photo']);

        if ($request->hasFile('photo')) {
            if ($member->photo_path) {
                Storage::disk('public')->delete($member->photo_path);
            }

            $data['photo_path'] = $request->file('photo')->store('member-photos', 'public');
        }

        $member->update($data);

        return redirect()
            ->route('members.show', $member)
            ->with('status', 'Data jemaat berhasil diperbarui.');
    }

    public function destroy(Member $member): RedirectResponse
    {
        $this->authorize('delete', $member);

        if ($member->photo_path) {
            Storage::disk('public')->delete($member->photo_path);
        }

        $familyId = $member->family_id;
        $name = $member->full_name;
        $member->delete();

        return redirect()
            ->route('families.show', $familyId)
            ->with('status', "Data {$name} berhasil dihapus.");
    }

    public function me(Request $request): RedirectResponse
    {
        $member = $request->user()->member;

        if (! $member) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Akun Anda belum ditautkan ke data jemaat. Silakan hubungi sekretariat gereja.');
        }

        return redirect()->route('members.show', $member);
    }

    /**
     * @return array<string, mixed>
     */
    private function memberData(MemberRequest $request, KtjNumberGenerator $ktjNumbers, ?Member $member = null): array
    {
        $data = $request->validated();

        $family = Family::query()->findOrFail($data['family_id']);
        $familyStatus = (string) $data['family_status'];
        $gender = Gender::from((string) $data['gender']);

        $childOrder = $data['child_order'] ?? null;

        if ($familyStatus === Member::FAMILY_STATUS_ANGGOTA) {
            $childOrder = (int) $childOrder;
        } elseif ($member && $member->family_status === $familyStatus) {
            $childOrder = $member->child_order;
        } else {
            $childOrder = null;
        }

        $data['child_order'] = $childOrder;

        if ($data['marital_status'] !== Member::MARITAL_STATUS_MARRIED) {
            $data['spouse_name'] = null;
        }

        if ($member && filled($member->ktj_id) && ! $this->ktjSourceChanged($member, $data)) {
            return $data;
        }

        try {
            $data['ktj_id'] = $ktjNumbers->generate(
                $family,
                $familyStatus,
                $childOrder,
                $gender,
                $member?->getKey()
            );
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages(['ktj_id' => $exception->getMessage()]);
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function ktjSourceChanged(Member $member, array $data): bool
    {
        return (int) $member->family_id !== (int) $data['family_id']
            || $member->family_status !== $data['family_status']
            || (string) ($member->child_order ?? '') !== (string) ($data['child_order'] ?? '')
            || $member->gender?->value !== $data['gender'];
    }

    /**
     * @return Collection<int, Family>
     */
    private function families()
    {
        return Family::query()
            ->orderBy('head_name')
            ->get(['id', 'kkj_id', 'head_name']);
    }
}

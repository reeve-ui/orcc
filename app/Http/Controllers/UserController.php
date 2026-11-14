<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\UserRequest;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->with('member')
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(20);

        $roleCounts = User::query()
            ->selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        return view('users.index', [
            'users' => $users,
            'roleCounts' => $roleCounts,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('users.form', [
            'user' => new User(['role' => Role::Pengerja, 'is_active' => true]),
            'roles' => Role::options(),
            'members' => $this->members(),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $user = User::create($request->validated());

        return redirect()
            ->route('users.index')
            ->with('status', "Akun {$user->name} berhasil dibuat.");
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('users.form', [
            'user' => $user,
            'roles' => Role::options(),
            'members' => $this->members(),
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = $request->validated();

        if ($user->is(auth()->user())) {
            if ((int) $data['role'] !== Role::Admin->value || ! $data['is_active']) {
                throw ValidationException::withMessages([
                    'role' => 'Anda tidak dapat menurunkan role atau menonaktifkan akun Anda sendiri.',
                ]);
            }
        }

        if ($user->role === Role::Admin
            && (int) $data['role'] !== Role::Admin->value
            && User::query()->where('role', Role::Admin->value)->count() <= 1) {
            throw ValidationException::withMessages([
                'role' => 'Minimal harus ada satu akun admin yang aktif.',
            ]);
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()
            ->route('users.index')
            ->with('status', "Akun {$user->name} berhasil diperbarui.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->role === Role::Admin && User::query()->where('role', Role::Admin->value)->count() <= 1) {
            return back()->with('error', 'Akun admin terakhir tidak boleh dihapus.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('status', "Akun {$name} berhasil dihapus.");
    }

    /**
     * @return Collection<int, Member>
     */
    private function members()
    {
        return Member::query()
            ->with('family')
            ->orderBy('full_name')
            ->get();
    }
}

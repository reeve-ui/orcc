@extends('layouts.app')

@section('title', 'Pengguna')
@section('eyebrow', 'Administrasi')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="page-title">Pengguna</h1>
                <span class="badge bg-slate-100 text-slate-600 ring-slate-500/20 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-600/40">{{ number_format($users->total(), 0, ',', '.') }}</span>
            </div>
            <p class="page-subtitle">Kelola akun dan batas akses admin, leader, pengerja, serta jemaat.</p>
        </div>
        @can('create', \App\Models\User::class)
            <a href="{{ route('users.create') }}" class="btn-primary">
                <x-icon name="plus" class="h-4 w-4" />
                Tambah Pengguna
            </a>
        @endcan
    </div>

    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        @foreach (\App\Enums\Role::cases() as $role)
            <div class="card card-pad">
                <div class="flex items-center justify-between gap-2">
                    <span class="badge {{ $role->badgeClasses() }}">{{ $role->label() }}</span>
                    <span class="text-lg font-extrabold text-slate-900 dark:text-white">{{ $roleCounts->get($role->value, 0) }}</span>
                </div>
                <p class="mt-2 text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">{{ $role->description() }}</p>
            </div>
        @endforeach
    </div>

    <div class="card overflow-hidden">
        <div class="table-shell">
            <table class="table-base">
                <thead>
                    <tr>
                        <th class="th">Nama</th>
                        <th class="th">Role</th>
                        <th class="th">Terhubung ke</th>
                        <th class="th">Status</th>
                        <th class="th">Terakhir masuk</th>
                        <th class="th text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($users as $user)
                        <tr class="table-row">
                            <td class="td">
                                <div class="flex items-center gap-3">
                                    <x-avatar :name="$user->name" size="h-9 w-9" text="text-[10px]" />
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $user->name }}</p>
                                        <p class="truncate text-[11px] text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="td"><span class="badge {{ $user->role->badgeClasses() }}">{{ $user->role->label() }}</span></td>
                            <td class="td">
                                @if ($user->member)
                                    <a href="{{ route('members.show', $user->member) }}" class="block max-w-[190px] truncate text-xs font-medium text-brand-700 hover:underline dark:text-brand-300">{{ $user->member->full_name }}</a>
                                    <p class="font-mono text-[10px] text-slate-400 dark:text-slate-500">{{ $user->member->family?->kkj_id }}</p>
                                @else
                                    <span class="text-xs text-slate-400 dark:text-slate-500">Tidak ditautkan</span>
                                @endif
                            </td>
                            <td class="td">
                                @if ($user->is_active)
                                    <span class="badge bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/30">Aktif</span>
                                @else
                                    <span class="badge bg-slate-100 text-slate-500 ring-slate-500/20 dark:bg-slate-800 dark:text-slate-400 dark:ring-slate-600/40">Nonaktif</span>
                                @endif
                            </td>
                            <td class="td text-xs text-slate-500 dark:text-slate-400">{{ $user->last_login_at?->translatedFormat('d M Y, H:i') ?? 'Belum pernah' }}</td>
                            <td class="td">
                                <div class="flex items-center justify-end gap-1">
                                    @can('update', $user)
                                        <a href="{{ route('users.edit', $user) }}" class="icon-btn" title="Ubah akun">
                                            <x-icon name="edit" class="h-4 w-4" />
                                        </a>
                                    @endcan
                                    @can('delete', $user)
                                        <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Hapus akun {{ $user->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="icon-btn text-rose-500 hover:bg-rose-50 hover:text-rose-700 dark:hover:bg-rose-500/10" title="Hapus akun">
                                                <x-icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state title="Belum ada pengguna" description="Tambahkan akun untuk sekretariat atau tim pelayanan." icon="shield" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$users" />
    </div>
@endsection

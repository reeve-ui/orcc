@extends('layouts.app')

@section('title', $user->exists ? 'Ubah Pengguna' : 'Tambah Pengguna')
@section('eyebrow', 'Administrasi')

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('users.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali ke daftar pengguna
        </a>
    </div>

    <div>
        <h1 class="page-title">{{ $user->exists ? 'Ubah Akun Pengguna' : 'Tambah Akun Pengguna' }}</h1>
        <p class="page-subtitle">Role menentukan batas akses data dan fitur dalam aplikasi.</p>
    </div>

    <form method="POST"
          action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}"
          class="space-y-5">
        @csrf
        @if ($user->exists)
            @method('PUT')
        @endif

        <div class="card">
            <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800 sm:px-6">
                <h2 class="section-title">Akun</h2>
            </div>
            <div class="card-pad grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="label">Nama <span class="text-rose-500">*</span></label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus
                           class="input @error('name') input-error @enderror">
                    @error('name') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="label">Email <span class="text-rose-500">*</span></label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                           class="input @error('email') input-error @enderror">
                    @error('email') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="role" class="label">Role <span class="text-rose-500">*</span></label>
                    <select id="role" name="role" required class="input @error('role') input-error @enderror">
                        @foreach ($roles as $value => $label)
                            <option value="{{ $value }}" @selected((int) old('role', $user->role?->value ?? \App\Enums\Role::Jemaat->value) === (int) $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('role') <p class="error-text">{{ $message }}</p> @enderror
                    <p class="hint">Admin: akses penuh · Leader: kelola & hapus · Pengerja: kelola · Jemaat: keluarga sendiri.</p>
                </div>

                <div>
                    <label for="member_id" class="label">Tautkan ke Data Jemaat</label>
                    <select id="member_id" name="member_id" class="input @error('member_id') input-error @enderror">
                        <option value="">Tidak ditautkan</option>
                        @foreach ($members as $memberOption)
                            <option value="{{ $memberOption->id }}" @selected((int) old('member_id', $user->member_id) === $memberOption->id)>
                                {{ $memberOption->full_name }} · {{ $memberOption->family?->kkj_id }}
                            </option>
                        @endforeach
                    </select>
                    @error('member_id') <p class="error-text">{{ $message }}</p> @enderror
                    <p class="hint">Wajib ditautkan untuk akun dengan role Jemaat agar dapat melihat keluarganya.</p>
                </div>

                <div class="sm:col-span-2">
                    <label class="flex items-center gap-2.5 text-xs font-medium text-slate-600 dark:text-slate-400">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               @checked(old('is_active', $user->is_active ?? true))
                               class="h-4 w-4 rounded border-slate-300 text-brand-700 focus:ring-brand-500 dark:border-slate-700 dark:bg-slate-950">
                        Akun aktif dan dapat masuk
                    </label>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800 sm:px-6">
                <h2 class="section-title">Kata sandi</h2>
                <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                    {{ $user->exists ? 'Kosongkan jika tidak ingin mengubah kata sandi.' : 'Minimal 8 karakter.' }}
                </p>
            </div>
            <div class="card-pad grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="password" class="label">Kata sandi {{ $user->exists ? '' : '*' }}</label>
                    <input id="password" name="password" type="password" autocomplete="new-password"
                           class="input @error('password') input-error @enderror">
                    @error('password') <p class="error-text">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="label">Ulangi kata sandi</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                           class="input">
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <a href="{{ route('users.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">
                <x-icon name="check" class="h-4 w-4" />
                {{ $user->exists ? 'Simpan Perubahan' : 'Buat Akun' }}
            </button>
        </div>
    </form>
@endsection

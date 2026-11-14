@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    <div class="card p-6 sm:p-8">
        <p class="eyebrow">Portal jemaat</p>
        <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Masuk ke akun Anda</h1>
        <p class="mt-2 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
            Gunakan email dan kata sandi yang diberikan oleh sekretariat gereja.
        </p>

        <form method="POST" action="{{ route('login.store') }}" class="mt-7 space-y-5">
            @csrf

            <div>
                <label for="email" class="label">Email</label>
                <div class="relative">
                    <x-icon name="mail" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input id="email"
                           name="email"
                           type="email"
                           value="{{ old('email') }}"
                           required
                           autofocus
                           autocomplete="username"
                           placeholder="nama@email.com"
                           class="input pl-10 @error('email') input-error @enderror">
                </div>
                @error('email')
                    <p class="error-text">{{ $message }}</p>
                @enderror
            </div>

            <div x-data="{ show: false }">
                <div class="flex items-center justify-between">
                    <label for="password" class="label">Kata sandi</label>
                    <span class="mb-1.5 text-[11px] text-slate-400 dark:text-slate-500">Lupa? Hubungi sekretariat</span>
                </div>
                <div class="relative">
                    <x-icon name="shield" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input id="password"
                           name="password"
                           type="password"
                           x-bind:type="show ? 'text' : 'password'"
                           required
                           autocomplete="current-password"
                           placeholder="••••••••"
                           class="input pl-10 pr-11 @error('password') input-error @enderror">
                    <button type="button"
                            class="absolute right-2 top-1/2 inline-flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                            @click="show = !show"
                            :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                        <x-icon name="eye" class="h-4 w-4" />
                    </button>
                </div>
                @error('password')
                    <p class="error-text">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2.5 text-xs font-medium text-slate-600 dark:text-slate-400">
                <input type="checkbox"
                       name="remember"
                       value="1"
                       @checked(old('remember'))
                       class="h-4 w-4 rounded border-slate-300 text-brand-700 focus:ring-brand-500 dark:border-slate-700 dark:bg-slate-950">
                Ingat saya di perangkat ini
            </label>

            <button type="submit" class="btn-primary w-full">
                Masuk
                <x-icon name="chevron-right" class="h-4 w-4" />
            </button>
        </form>

    </div>
@endsection

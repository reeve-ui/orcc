@php
    $user = auth()->user();
@endphp

<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f766e">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script>
        (function () {
            try {
                var stored = localStorage.getItem('theme');
                var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (stored === 'dark' || (!stored && prefersDark)) {
                    document.documentElement.classList.add('dark');
                }
            } catch (error) {
                // Abaikan bila localStorage tidak tersedia.
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full" x-data="{ sidebarOpen: false }">
    <div x-cloak
         x-show="sidebarOpen"
         x-transition.opacity
         class="fixed inset-0 z-40 bg-slate-950/50 backdrop-blur-sm lg:hidden"
         @click="sidebarOpen = false"></div>

    <aside class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-slate-200/80 bg-white/95 backdrop-blur-xl transition-transform duration-300 ease-out dark:border-slate-800 dark:bg-slate-950/95 lg:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        <div class="flex h-20 items-center gap-3 px-5">
            <x-church-logo class="h-11 w-11" icon-class="h-6 w-6" />
            <div class="min-w-0">
                <p class="truncate text-sm font-extrabold tracking-tight text-slate-900 dark:text-white">{{ config('church.name') }}</p>
                <p class="truncate text-[11px] font-medium text-slate-500 dark:text-slate-400">{{ config('church.tagline') }}</p>
            </div>
            <button type="button" class="icon-btn ml-auto lg:hidden" @click="sidebarOpen = false" aria-label="Tutup menu">
                <x-icon name="close" class="h-5 w-5" />
            </button>
        </div>

        <nav class="flex-1 space-y-6 overflow-y-auto px-4 pb-6">
            @if ($user->isStaff())
                <div>
                    <p class="nav-heading">Ruang kerja</p>
                    <div class="space-y-1">
                        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'sidebar-link-active' : '' }}">
                            <x-icon name="dashboard" class="h-5 w-5" />
                            Dashboard
                        </a>
                        <a href="{{ route('families.index') }}" class="sidebar-link {{ request()->routeIs('families.*') ? 'sidebar-link-active' : '' }}">
                            <x-icon name="family" class="h-5 w-5" />
                            Data KKJ
                        </a>
                        <a href="{{ route('members.index') }}" class="sidebar-link {{ request()->routeIs('members.*') ? 'sidebar-link-active' : '' }}">
                            <x-icon name="users" class="h-5 w-5" />
                            Data Jemaat
                        </a>
                    </div>
                </div>

                <div>
                    <p class="nav-heading">Kartu & administrasi</p>
                    <div class="space-y-1">
                        <a href="{{ route('families.index') }}" class="sidebar-link">
                            <x-icon name="card" class="h-5 w-5" />
                            Kartu KKJ
                        </a>
                        @if ($user->isAdmin())
                            <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'sidebar-link-active' : '' }}">
                                <x-icon name="shield" class="h-5 w-5" />
                                Pengguna
                            </a>
                            <a href="{{ route('settings.edit') }}" class="sidebar-link {{ request()->routeIs('settings.*') ? 'sidebar-link-active' : '' }}">
                                <x-icon name="settings" class="h-5 w-5" />
                                Pengaturan Gereja
                            </a>
                        @endif
                    </div>
                </div>
            @else
                <div>
                    <p class="nav-heading">Ruang jemaat</p>
                    <div class="space-y-1">
                        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'sidebar-link-active' : '' }}">
                            <x-icon name="dashboard" class="h-5 w-5" />
                            Dashboard
                        </a>
                        <a href="{{ route('my.family') }}" class="sidebar-link">
                            <x-icon name="family" class="h-5 w-5" />
                            Keluarga Saya
                        </a>
                        <a href="{{ route('my.profile') }}" class="sidebar-link">
                            <x-icon name="member" class="h-5 w-5" />
                            Profil Saya
                        </a>
                    </div>
                </div>
            @endif

            <div class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-900/60">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-700 dark:text-slate-200">
                    <x-icon name="shield" class="h-4 w-4 text-brand-600 dark:text-brand-400" />
                    Privasi data
                </div>
                <p class="mt-1.5 text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">
                    Data pribadi jemaat hanya digunakan untuk pelayanan gereja. Jangan membagikan tangkapan layar atau dokumen di luar kebutuhan pelayanan.
                </p>
            </div>
        </nav>

        <div class="border-t border-slate-200/80 p-4 dark:border-slate-800">
            <div class="flex items-center gap-3 rounded-2xl bg-slate-50/80 p-3 dark:bg-slate-900/60">
                <x-avatar :name="$user->name" size="h-9 w-9" text="text-[11px]" />
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ $user->name }}</p>
                    <span class="badge mt-1 {{ $user->role->badgeClasses() }}">{{ $user->role->label() }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="icon-btn" title="Keluar" aria-label="Keluar">
                        <x-icon name="logout" class="h-4 w-4" />
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="min-h-screen lg:pl-72">
        <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-slate-200/70 bg-[#f6f6f3]/80 px-4 backdrop-blur-xl dark:border-slate-800/80 dark:bg-[#080d16]/80 sm:px-6 lg:px-8">
            <button type="button" class="icon-btn lg:hidden" @click="sidebarOpen = true" aria-label="Buka menu">
                <x-icon name="menu" class="h-5 w-5" />
            </button>

            <div class="hidden min-w-0 sm:block">
                <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">@yield('eyebrow', 'Ruang kerja')</p>
                <p class="truncate text-sm font-bold text-slate-900 dark:text-white">@yield('title', 'Dashboard')</p>
            </div>

            <div class="ml-auto flex items-center gap-2">
                @if ($user->isStaff())
                    <form method="GET" action="{{ route('members.index') }}" class="hidden md:block">
                        <div class="relative">
                            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                            <input type="search"
                                   name="q"
                                   value="{{ request()->routeIs('members.index') ? request('q') : '' }}"
                                   placeholder="Cari nama atau KKJ…"
                                   class="h-9 w-56 rounded-full border border-slate-200 bg-white/80 pl-9 pr-4 text-xs text-slate-700 shadow-sm transition placeholder:text-slate-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/25 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-200 dark:placeholder:text-slate-500">
                        </div>
                    </form>
                @endif

                <button type="button" onclick="window.toggleTheme()" class="icon-btn" title="Ganti mode terang/gelap" aria-label="Ganti mode terang atau gelap">
                    <x-icon name="sun" class="h-4 w-4 dark:hidden" />
                    <x-icon name="moon" class="hidden h-4 w-4 dark:block" />
                </button>
            </div>
        </header>

        <main class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            <div class="mx-auto max-w-7xl space-y-6">
                @if (session('status'))
                    <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
                        <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0" />
                        <p>{{ session('status') }}</p>
                    </div>
                @endif

                @if (session('error'))
                    <div class="flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">
                        <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
                        <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
                        <div>
                            <p class="font-semibold">Periksa kembali isian berikut:</p>
                            <ul class="mt-1 list-inside list-disc space-y-0.5 text-xs">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @if ($user->isAdmin() && preg_match('/^[A-Z0-9]{5}$/', strtoupper((string) config('church.code'))) !== 1)
                    <div class="flex flex-col gap-3 rounded-2xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-900 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-100 sm:flex-row sm:items-center">
                        <x-icon name="settings" class="h-5 w-5 shrink-0" />
                        <div class="flex-1">
                            <p class="font-semibold">Lengkapi pengaturan gereja</p>
                            <p class="mt-0.5 text-xs opacity-80">Isi kode gereja 5 karakter dan identitas gereja agar nomor KKJ baru dapat dibuat otomatis.</p>
                        </div>
                        <a href="{{ route('settings.edit') }}" class="btn-primary shrink-0">Buka pengaturan</a>
                    </div>
                @endif

                @yield('content')

                <footer class="flex flex-col gap-1 border-t border-slate-200/70 pt-5 text-[11px] text-slate-400 dark:border-slate-800/80 dark:text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                    <p>{{ config('church.name') }} · Sistem Data Jemaat</p>
                    <p>Dikelola dengan {{ config('app.name') }} v{{ config('app.version') }} · {{ now()->year }}</p>
                </footer>
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>

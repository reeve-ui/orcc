<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f766e">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>@yield('title', 'Verifikasi Kartu KKJ') · {{ config('app.name') }}</title>

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
<body class="min-h-full">
    <div class="pointer-events-none fixed inset-x-0 top-0 -z-10 h-72 bg-gradient-to-b from-brand-100/70 to-transparent dark:from-brand-500/10"></div>

    <header class="mx-auto flex w-full max-w-3xl items-center justify-between px-5 py-6 sm:px-8">
        <a href="{{ url('/') }}" class="flex items-center gap-3">
            <x-church-logo class="h-11 w-11" icon-class="h-6 w-6" />
            <span>
                <span class="block text-sm font-extrabold tracking-tight text-slate-900 dark:text-white">{{ config('church.name') }}</span>
                <span class="block text-[11px] font-medium text-slate-500 dark:text-slate-400">{{ config('church.tagline') }}</span>
            </span>
        </a>

        <button type="button" onclick="window.toggleTheme()" class="icon-btn" title="Ganti mode terang/gelap" aria-label="Ganti mode terang atau gelap">
            <x-icon name="sun" class="h-4 w-4 dark:hidden" />
            <x-icon name="moon" class="hidden h-4 w-4 dark:block" />
        </button>
    </header>

    <main class="mx-auto w-full max-w-3xl px-5 pb-14 pt-2 sm:px-8">
        @yield('content')
    </main>

    <footer class="mx-auto w-full max-w-3xl px-5 pb-10 text-center text-[11px] leading-relaxed text-slate-400 dark:text-slate-500 sm:px-8">
        &copy; {{ now()->year }} {{ config('app.name') }} v{{ config('app.version') }}. Halaman verifikasi ini hanya menampilkan ringkasan data keluarga.
    </footer>
</body>
</html>

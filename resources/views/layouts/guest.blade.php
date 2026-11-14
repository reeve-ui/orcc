<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f766e">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>@yield('title', 'Masuk') · {{ config('app.name') }}</title>

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
    <div class="grid min-h-screen lg:grid-cols-[1.05fr_1fr]">
        <aside class="relative hidden overflow-hidden bg-brand-900 px-12 py-14 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-brand-500/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-32 -left-16 h-96 w-96 rounded-full bg-emerald-400/10 blur-3xl"></div>
            <div class="pointer-events-none absolute inset-0 opacity-[0.07]" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 26px 26px;"></div>

            <div class="relative">
                <div class="flex items-center gap-3">
                    <x-church-logo class="h-12 w-12" icon-class="h-6 w-6" />
                    <div>
                        <p class="text-sm font-extrabold tracking-tight">{{ config('church.name') }}</p>
                        <p class="text-[11px] font-medium text-brand-100/80">{{ config('church.tagline') }}</p>
                    </div>
                </div>

                <h1 class="mt-16 max-w-lg text-4xl font-extrabold leading-tight tracking-tight">
                    Satu data,<br>satu keluarga<br>pelayanan.
                </h1>
                <p class="mt-5 max-w-md text-sm leading-relaxed text-brand-100/80">
                    Kelola data KKJ dan jemaat dengan rapi, pantau demografi pelayanan, dan cetak Kartu Keluarga Jemaat dalam satu tempat.
                </p>

                <div class="mt-10 space-y-4">
                    @foreach ([
                        ['icon' => 'shield', 'title' => 'Akses berjenjang', 'text' => 'Admin, leader, pengerja, dan jemaat memiliki batas akses yang jelas.'],
                        ['icon' => 'dashboard', 'title' => 'Dashboard pelayanan', 'text' => 'Jumlah jiwa, gender, kategori usia, dan sebaran wilayah.'],
                        ['icon' => 'card', 'title' => 'Kartu KKJ siap cetak', 'text' => 'Unduh kartu keluarga per nomor KKJ dalam format PDF.'],
                    ] as $feature)
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/15">
                                <x-icon :name="$feature['icon']" class="h-4 w-4" />
                            </span>
                            <div>
                                <p class="text-xs font-bold">{{ $feature['title'] }}</p>
                                <p class="mt-0.5 text-[11px] leading-relaxed text-brand-100/70">{{ $feature['text'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <p class="relative text-[11px] text-brand-100/60">
                &copy; {{ now()->year }} {{ config('app.name') }} v{{ config('app.version') }}. Data pribadi dilindungi dan hanya digunakan untuk pelayanan.
            </p>
        </aside>

        <main class="relative flex items-center justify-center px-5 py-10 sm:px-10">
            <button type="button" onclick="window.toggleTheme()" class="icon-btn absolute right-5 top-5" title="Ganti mode terang/gelap" aria-label="Ganti mode terang atau gelap">
                <x-icon name="sun" class="h-4 w-4 dark:hidden" />
                <x-icon name="moon" class="hidden h-4 w-4 dark:block" />
            </button>

            <div class="w-full max-w-md">
                <div class="mb-10 flex items-center gap-3 lg:hidden">
                    <x-church-logo class="h-11 w-11" icon-class="h-6 w-6" />
                    <div>
                        <p class="text-sm font-extrabold tracking-tight text-slate-900 dark:text-white">{{ config('church.name') }}</p>
                        <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">{{ config('church.tagline') }}</p>
                    </div>
                </div>

                @if (session('status'))
                    <div class="mb-5 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
                        <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0" />
                        <p>{{ session('status') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">
                        <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
                        <p>{{ $errors->first() }}</p>
                    </div>
                @endif

                @yield('content')

                <p class="mt-8 text-center text-[11px] leading-relaxed text-slate-400 dark:text-slate-500">
                    Butuh akses? Hubungi sekretariat gereja.<br>
                    Jangan bagikan kata sandi kepada siapa pun.
                </p>
            </div>
        </main>
    </div>
</body>
</html>

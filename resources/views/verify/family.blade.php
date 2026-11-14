@extends('layouts.public')

@section('title', 'Verifikasi Kartu KKJ '.$family->kkj_id)

@section('content')
    <div class="card overflow-hidden">
        <div class="relative overflow-hidden bg-gradient-to-br from-brand-900 via-brand-800 to-brand-600 px-6 py-8 text-white sm:px-9 sm:py-10">
            <div class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
            <div class="relative">
                <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-brand-100/80">Verifikasi Kartu KKJ</p>
                <h1 class="mt-2 text-2xl font-extrabold tracking-tight sm:text-3xl">{{ $family->head_name }}</h1>
                <p class="mt-2 max-w-lg text-sm leading-relaxed text-brand-100/85">
                    Kartu Keluarga Jemaat ini terhubung dengan data resmi {{ $church['name'] }}.
                </p>

                <div class="mt-5 inline-flex items-center gap-2 rounded-full bg-emerald-400/15 px-3 py-1.5 text-xs font-bold text-emerald-50 ring-1 ring-emerald-300/30">
                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-400/20">
                        <x-icon name="check" class="h-3 w-3" />
                    </span>
                    Terdaftar di Sistem Data Jemaat
                </div>
            </div>
        </div>

        <div class="card-pad">
            <dl class="grid gap-5 sm:grid-cols-2">
                <div>
                    <dt class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500">Nomor KKJ</dt>
                    <dd class="mt-1 font-mono text-sm font-bold text-slate-900 dark:text-white">{{ $family->kkj_id }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500">Kepala Keluarga</dt>
                    <dd class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ $family->head_name }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500">Jumlah Anggota Terdata</dt>
                    <dd class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ $family->members_count }} orang</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500">Wilayah</dt>
                    <dd class="mt-1 text-sm font-bold text-slate-900 dark:text-white">
                        {{ collect([$family->village, $family->district, $family->city])->filter()->implode(', ') ?: 'Belum dilengkapi' }}
                    </dd>
                </div>
                @if ($family->joined_year)
                    <div>
                        <dt class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500">Tahun Masuk</dt>
                        <dd class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ $family->joined_year }}</dd>
                    </div>
                @endif
            </dl>

            <div class="mt-6 flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-3 text-[11px] leading-relaxed text-slate-600 dark:border-slate-800 dark:bg-slate-950/50 dark:text-slate-400">
                <x-icon name="shield" class="mt-0.5 h-4 w-4 shrink-0 text-brand-600 dark:text-brand-400" />
                <p>
                    Halaman ini sengaja hanya menampilkan ringkasan. NIK, tanggal lahir, alamat lengkap, dan kontak
                    tidak ditampilkan kepada publik. Jika data pada kartu tidak sesuai, hubungi sekretariat gereja.
                </p>
            </div>

            @if ($church['email'] || $church['phone'])
                <p class="mt-4 text-center text-[11px] text-slate-500 dark:text-slate-400">
                    Sekretariat:
                    @if ($church['phone'])
                        <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $church['phone'] }}</span>
                    @endif
                    @if ($church['phone'] && $church['email'])
                        ·
                    @endif
                    @if ($church['email'])
                        <a href="mailto:{{ $church['email'] }}" class="font-semibold text-brand-700 hover:underline dark:text-brand-300">{{ $church['email'] }}</a>
                    @endif
                </p>
            @endif
        </div>
    </div>
@endsection

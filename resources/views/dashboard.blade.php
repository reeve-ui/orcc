@extends('layouts.app')

@section('title', 'Dashboard')
@section('eyebrow', 'Ringkasan pelayanan')

@php
    $ageCounts = $stats['age'];
    $maxAge = max(1, ...array_values($ageCounts));
    $gender = $stats['gender_share'];
    $maleEnd = $gender['male'];
    $femaleEnd = $gender['male'] + $gender['female'];
    $maxMarital = max(1, ...array_values($stats['marital'] ?: [0]));
    $maxDistrict = max(1, (int) ($stats['districts']->max('total') ?? 1));
    $firstName = \Illuminate\Support\Str::before(auth()->user()->name, ' ');
@endphp

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="eyebrow">{{ now()->translatedFormat('l, d F Y') }}</p>
            <h1 class="page-title mt-1">Selamat datang, {{ $firstName }}.</h1>
            <p class="page-subtitle">Ringkasan data jemaat {{ config('church.name') }}.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('families.index') }}" class="btn-secondary">
                <x-icon name="card" class="h-4 w-4" />
                Kartu KKJ
            </a>
            @can('create', \App\Models\Member::class)
                <a href="{{ route('members.create') }}" class="btn-primary">
                    <x-icon name="plus" class="h-4 w-4" />
                    Tambah Jemaat
                </a>
            @endcan
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Total KKJ" :value="number_format($stats['families'], 0, ',', '.')" hint="{{ number_format($stats['head_of_family'], 0, ',', '.') }} kepala keluarga terdata" icon="family" tone="brand" />
        <x-stat-card label="Total Jemaat" :value="number_format($stats['members'], 0, ',', '.')" hint="Rata-rata {{ number_format($stats['families'] > 0 ? $stats['members'] / $stats['families'] : 0, 1, ',', '.') }} jiwa per KKJ" icon="users" tone="sky" />
        <x-stat-card label="Pria" :value="number_format($stats['male'], 0, ',', '.')" hint="{{ $stats['gender_share']['male'] }}% dari total jemaat" icon="member" tone="violet" />
        <x-stat-card label="Wanita" :value="number_format($stats['female'], 0, ',', '.')" hint="{{ $stats['gender_share']['female'] }}% dari total jemaat" icon="member" tone="pink" />
    </div>

    @if ($stats['incomplete'] > 0)
        <div class="flex flex-col gap-3 rounded-2xl border border-amber-200 bg-amber-50/80 px-4 py-3.5 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-3">
                <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
                <p>
                    <span class="font-bold">{{ number_format($stats['incomplete'], 0, ',', '.') }} data jemaat</span>
                    belum lengkap (tanggal lahir atau jenis kelamin). Data lama hasil impor perlu dilengkapi agar statistik usia akurat.
                </p>
            </div>
            <a href="{{ route('members.index') }}" class="btn-secondary btn-sm shrink-0">Lengkapi data</a>
        </div>
    @endif

    <div class="grid gap-5 lg:grid-cols-5">
        <div class="card card-pad lg:col-span-2">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="section-title">Komposisi gender</h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Berdasarkan jenis kelamin jemaat.</p>
                </div>
                <span class="badge bg-slate-100 text-slate-600 ring-slate-500/20 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-600/40">{{ number_format($stats['members'], 0, ',', '.') }} jiwa</span>
            </div>

            <div class="mt-6 flex flex-col items-center gap-6 sm:flex-row">
                <div class="relative h-36 w-36 shrink-0 rounded-full"
                     style="background: conic-gradient(#0d9488 0 {{ $maleEnd }}%, #ec4899 {{ $maleEnd }}% {{ $femaleEnd }}%, #cbd5e1 {{ $femaleEnd }}% 100%);">
                    <div class="absolute inset-4 flex flex-col items-center justify-center rounded-full bg-white dark:bg-slate-900">
                        <span class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $stats['gender_share']['male'] }}%</span>
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">pria</span>
                    </div>
                </div>

                <div class="w-full space-y-3">
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <span class="flex items-center gap-2 font-medium text-slate-600 dark:text-slate-300">
                            <span class="h-2.5 w-2.5 rounded-full bg-brand-600"></span> Pria
                        </span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ number_format($stats['male'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <span class="flex items-center gap-2 font-medium text-slate-600 dark:text-slate-300">
                            <span class="h-2.5 w-2.5 rounded-full bg-pink-500"></span> Wanita
                        </span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ number_format($stats['female'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <span class="flex items-center gap-2 font-medium text-slate-600 dark:text-slate-300">
                            <span class="h-2.5 w-2.5 rounded-full bg-slate-300 dark:bg-slate-600"></span> Belum diisi
                        </span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ number_format($stats['unknown_gender'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-pad lg:col-span-3">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="section-title">Kategori usia</h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Batas usia dapat diubah di <code class="rounded bg-slate-100 px-1 py-0.5 text-[10px] dark:bg-slate-800">config/church.php</code>.</p>
                </div>
                <a href="{{ route('members.index') }}" class="text-xs font-semibold text-brand-700 hover:underline dark:text-brand-300">Lihat data</a>
            </div>

            <div class="mt-6 space-y-4">
                @foreach ($ageCounts as $label => $count)
                    <div class="flex items-center gap-3">
                        <span class="w-16 shrink-0 text-xs font-semibold text-slate-600 dark:text-slate-300">{{ $label }}</span>
                        <div class="bar-track flex-1">
                            <div class="h-full rounded-full transition-all duration-500"
                                 style="width: {{ min(100, $count / $maxAge * 100) }}%; background: {{ \App\Support\AgeCategory::barColor($label) }};"></div>
                        </div>
                        <span class="w-8 shrink-0 text-right text-xs font-bold text-slate-900 dark:text-white">{{ $count }}</span>
                    </div>
                @endforeach
            </div>

            <div class="mt-5 flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 text-[11px] text-slate-500 dark:bg-slate-800/60 dark:text-slate-400">
                <x-icon name="calendar" class="h-3.5 w-3.5 shrink-0" />
                {{ number_format($stats['unknown_age'], 0, ',', '.') }} jemaat belum memiliki tanggal lahir, sehingga belum masuk kategori usia.
            </div>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-3">
        <div class="card card-pad">
            <h2 class="section-title">Status pernikahan</h2>
            <div class="mt-5 space-y-4">
                @forelse ($stats['marital'] as $status => $count)
                    <div>
                        <div class="flex items-center justify-between gap-3 text-xs">
                            <span class="font-medium text-slate-600 dark:text-slate-300">{{ $status }}</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ number_format($count, 0, ',', '.') }}</span>
                        </div>
                        <div class="bar-track mt-1.5 h-2">
                            <div class="h-full rounded-full bg-brand-600/80" style="width: {{ min(100, $count / $maxMarital * 100) }}%;"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 dark:text-slate-400">Belum ada data status pernikahan.</p>
                @endforelse
            </div>
        </div>

        <div class="card card-pad">
            <div class="flex items-center justify-between gap-3">
                <h2 class="section-title">Sebaran wilayah</h2>
                <a href="{{ route('families.index') }}" class="text-[11px] font-semibold text-brand-700 hover:underline dark:text-brand-300">Semua</a>
            </div>
            <div class="mt-5 space-y-3.5">
                @forelse ($stats['districts'] as $district)
                    <div>
                        <div class="flex items-center justify-between gap-3 text-xs">
                            <span class="truncate font-medium text-slate-600 dark:text-slate-300">{{ $district->district }}</span>
                            <span class="shrink-0 font-bold text-slate-900 dark:text-white">{{ $district->total }} KKJ</span>
                        </div>
                        <div class="bar-track mt-1.5 h-2">
                            <div class="h-full rounded-full bg-sky-500/80" style="width: {{ min(100, $district->total / $maxDistrict * 100) }}%;"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 dark:text-slate-400">Belum ada data wilayah.</p>
                @endforelse
            </div>
        </div>

        <div class="card card-pad">
            <div class="flex items-center justify-between gap-3">
                <h2 class="section-title">Ulang tahun terdekat</h2>
                <span class="badge bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/30">30 hari</span>
            </div>
            <div class="mt-4 space-y-2.5">
                @forelse ($birthdays as $item)
                    <a href="{{ route('members.show', $item['member']) }}" class="flex items-center gap-3 rounded-xl px-2 py-2 transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <x-avatar :name="$item['member']->full_name" size="h-9 w-9" text="text-[10px]" />
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-bold text-slate-800 dark:text-slate-200">{{ $item['member']->full_name }}</p>
                            <p class="truncate text-[10px] text-slate-500 dark:text-slate-400">{{ $item['member']->family?->kkj_id }} · {{ $item['turning'] }} tahun</p>
                        </div>
                        <span class="shrink-0 text-right text-[10px] font-semibold {{ $item['next']->isToday() ? 'text-brand-700 dark:text-brand-300' : 'text-slate-500 dark:text-slate-400' }}">
                            {{ $item['next']->isToday() ? 'Hari ini' : $item['next']->translatedFormat('d M') }}
                        </span>
                    </a>
                @empty
                    <p class="text-xs text-slate-500 dark:text-slate-400">Tidak ada ulang tahun dalam 30 hari ke depan.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 dark:border-slate-800 sm:px-6">
            <div>
                <h2 class="section-title">KKJ terbaru</h2>
                <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">Enam data keluarga yang terakhir ditambahkan.</p>
            </div>
            <a href="{{ route('families.index') }}" class="btn-ghost btn-sm">Lihat semua <x-icon name="chevron-right" class="h-3.5 w-3.5" /></a>
        </div>

        <div class="table-shell">
            <table class="table-base">
                <thead>
                    <tr>
                        <th class="th">Kepala Keluarga</th>
                        <th class="th">Nomor KKJ</th>
                        <th class="th">Wilayah</th>
                        <th class="th text-center">Anggota</th>
                        <th class="th text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($recentFamilies as $family)
                        <tr class="table-row">
                            <td class="td">
                                <div class="flex items-center gap-3">
                                    <x-avatar :name="$family->head_name" :src="$family->photo_url" size="h-9 w-9" text="text-[10px]" />
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $family->head_name }}</p>
                                        <p class="truncate text-[11px] text-slate-500 dark:text-slate-400">{{ $family->village ?: 'Kelurahan belum diisi' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="td"><span class="font-mono text-xs font-semibold">{{ $family->kkj_id }}</span></td>
                            <td class="td text-xs">{{ $family->district ?: '—' }}</td>
                            <td class="td text-center"><span class="badge bg-slate-100 text-slate-600 ring-slate-500/20 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-600/40">{{ $family->members_count }} orang</span></td>
                            <td class="td text-right">
                                <a href="{{ route('families.show', $family) }}" class="btn-ghost btn-sm">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state title="Belum ada data KKJ" description="Mulai dengan menambahkan data keluarga atau impor dari Excel." icon="family">
                                    @can('create', \App\Models\Family::class)
                                        <a href="{{ route('families.create') }}" class="btn-primary btn-sm">Tambah KKJ</a>
                                    @endcan
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

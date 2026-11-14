@extends('layouts.app')

@section('title', 'Dashboard')
@section('eyebrow', 'Ruang jemaat')

@php
    $family = $member?->family;
    $firstName = \Illuminate\Support\Str::before(auth()->user()->name, ' ');
    $familyMembers = $family
        ? $family->members->sortBy(fn ($item) => sprintf('%02d-%02d-%s', $item->status_rank, $item->child_order ?? 99, $item->full_name))->values()
        : collect();
@endphp

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="eyebrow">{{ now()->translatedFormat('l, d F Y') }}</p>
            <h1 class="page-title mt-1">Selamat datang, {{ $firstName }}.</h1>
            <p class="page-subtitle">Ringkasan data keluarga dan profil Anda.</p>
        </div>
        @if ($family)
            <a href="{{ route('families.card.download', $family) }}" class="btn-primary">
                <x-icon name="download" class="h-4 w-4" />
                Unduh Kartu KKJ
            </a>
        @endif
    </div>

    @if (! $member)
        <div class="card card-pad">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300">
                    <x-icon name="alert" class="h-6 w-6" />
                </span>
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Akun Anda belum terhubung ke data jemaat</h2>
                    <p class="mt-1.5 max-w-2xl text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                        Saat ini akun Anda belum ditautkan ke data anggota keluarga. Hubungi sekretariat gereja agar akun ini dihubungkan ke data Anda, sehingga Kartu Keluarga Jemaat dapat ditampilkan.
                    </p>
                    @if (config('church.email'))
                        <a href="mailto:{{ config('church.email') }}" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-brand-700 hover:underline dark:text-brand-300">
                            <x-icon name="mail" class="h-4 w-4" />
                            {{ config('church.email') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="grid gap-5 lg:grid-cols-5">
            <div class="card overflow-hidden lg:col-span-3">
                <div class="relative overflow-hidden bg-gradient-to-br from-brand-900 via-brand-800 to-brand-600 p-6 text-white sm:p-7">
                    <div class="pointer-events-none absolute -right-12 -top-16 h-52 w-52 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="pointer-events-none absolute -bottom-20 right-16 h-44 w-44 rounded-full bg-emerald-300/10 blur-2xl"></div>
                    <div class="relative">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-brand-100/80">Kartu Keluarga Jemaat</p>
                        <p class="mt-3 font-mono text-sm font-semibold tracking-wider text-brand-100">{{ $family?->kkj_id ?? 'KKJ belum tersedia' }}</p>
                        <h2 class="mt-1 text-2xl font-extrabold tracking-tight">{{ $family?->head_name ?? $member->full_name }}</h2>
                        <p class="mt-2 max-w-md text-xs leading-relaxed text-brand-100/80">{{ $family?->full_address ?? 'Alamat belum dilengkapi' }}</p>
                    </div>
                </div>

                <div class="card-pad">
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500">Anggota</p>
                            <p class="mt-1 text-lg font-extrabold text-slate-900 dark:text-white">{{ $familyMembers->count() }} orang</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500">Wilayah</p>
                            <p class="mt-1 truncate text-sm font-bold text-slate-900 dark:text-white">{{ $family?->district ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500">Tahun masuk</p>
                            <p class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ $family?->joined_year ?: '—' }}</p>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col gap-2 sm:flex-row">
                        @if ($family)
                            <a href="{{ route('families.card.download', $family) }}" class="btn-primary flex-1">
                                <x-icon name="download" class="h-4 w-4" />
                                Unduh PDF
                            </a>
                            <a href="{{ route('families.show', $family) }}" class="btn-secondary flex-1">
                                <x-icon name="family" class="h-4 w-4" />
                                Lihat Keluarga
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card card-pad lg:col-span-2">
                <div class="flex items-center gap-4">
                    <x-avatar :name="$member->full_name" :src="$member->photo_url" size="h-16 w-16" text="text-lg" />
                    <div class="min-w-0">
                        <h2 class="truncate text-base font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $member->full_name }}</h2>
                        <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $member->alias ?: 'Nama panggilan belum diisi' }}</p>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <span class="badge bg-brand-50 text-brand-700 ring-brand-600/20 dark:bg-brand-500/10 dark:text-brand-300 dark:ring-brand-500/30">{{ $member->family_status }}</span>
                            @if ($member->gender)
                                <span class="badge {{ $member->gender->badgeClasses() }}">{{ $member->gender->label() }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <dl class="mt-6 space-y-3 text-sm">
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-xs text-slate-500 dark:text-slate-400">Usia</dt>
                        <dd class="text-right text-xs font-semibold text-slate-900 dark:text-white">
                            {{ $member->age !== null ? $member->age.' tahun' : 'Belum diisi' }}
                            @if ($member->birth_date)
                                <span class="badge ml-1 {{ \App\Support\AgeCategory::badgeClasses($member->age_category) }}">{{ $member->age_category }}</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-xs text-slate-500 dark:text-slate-400">Tanggal lahir</dt>
                        <dd class="text-right text-xs font-semibold text-slate-900 dark:text-white">{{ $member->birth_date?->translatedFormat('d F Y') ?? 'Belum diisi' }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-xs text-slate-500 dark:text-slate-400">Status pernikahan</dt>
                        <dd class="text-right text-xs font-semibold text-slate-900 dark:text-white">{{ $member->marital_status ?: 'Belum diisi' }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-xs text-slate-500 dark:text-slate-400">WhatsApp</dt>
                        <dd class="text-right text-xs font-semibold text-slate-900 dark:text-white">{{ $member->whatsapp ?: ($member->phone ?: 'Belum diisi') }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-xs text-slate-500 dark:text-slate-400">Email</dt>
                        <dd class="max-w-[60%] truncate text-right text-xs font-semibold text-slate-900 dark:text-white">{{ $member->email ?: 'Belum diisi' }}</dd>
                    </div>
                </dl>

                <a href="{{ route('members.show', $member) }}" class="btn-secondary mt-6 w-full">
                    <x-icon name="member" class="h-4 w-4" />
                    Lihat Profil Lengkap
                </a>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 dark:border-slate-800 sm:px-6">
                <div>
                    <h2 class="section-title">Anggota keluarga</h2>
                    <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">{{ $family?->kkj_id }}</p>
                </div>
                <span class="badge bg-slate-100 text-slate-600 ring-slate-500/20 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-600/40">{{ $familyMembers->count() }} orang</span>
            </div>

            <div class="table-shell">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th class="th">Nama</th>
                            <th class="th">Status</th>
                            <th class="th">Gender</th>
                            <th class="th">Usia</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($familyMembers as $familyMember)
                            <tr class="table-row">
                                <td class="td">
                                    <div class="flex items-center gap-3">
                                        <x-avatar :name="$familyMember->full_name" :src="$familyMember->photo_url" size="h-9 w-9" text="text-[10px]" />
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-slate-900 dark:text-white">
                                                {{ $familyMember->full_name }}
                                                @if ($familyMember->is($member))
                                                    <span class="ml-1 text-[10px] font-bold text-brand-700 dark:text-brand-300">(Anda)</span>
                                                @endif
                                            </p>
                                            <p class="truncate text-[11px] text-slate-500 dark:text-slate-400">{{ $familyMember->alias ?: '—' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="td text-xs">{{ $familyMember->family_status }}</td>
                                <td class="td text-xs">{{ $familyMember->gender?->label() ?? '—' }}</td>
                                <td class="td text-xs">
                                    @if ($familyMember->age !== null)
                                        {{ $familyMember->age }} th
                                        <span class="badge ml-1 {{ \App\Support\AgeCategory::badgeClasses($familyMember->age_category) }}">{{ $familyMember->age_category }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex items-start gap-3 rounded-2xl border border-slate-200/80 bg-white/60 px-4 py-3 text-[11px] leading-relaxed text-slate-500 dark:border-slate-800 dark:bg-slate-900/50 dark:text-slate-400">
            <x-icon name="shield" class="mt-0.5 h-4 w-4 shrink-0 text-brand-600 dark:text-brand-400" />
            <p>Data pada halaman ini bersifat pribadi. Jika ada data yang perlu diperbarui, silakan hubungi sekretariat gereja agar perubahan tercatat dengan benar.</p>
        </div>
    @endif
@endsection

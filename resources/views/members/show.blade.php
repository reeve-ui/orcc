@extends('layouts.app')

@section('title', 'Profil Jemaat')
@section('eyebrow', 'Data jemaat')

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        @if (auth()->user()->isStaff())
            <a href="{{ route('members.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                <x-icon name="arrow-left" class="h-4 w-4" />
                Kembali ke data jemaat
            </a>
        @else
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                <x-icon name="arrow-left" class="h-4 w-4" />
                Kembali ke dashboard
            </a>
        @endif

        <div class="flex flex-wrap gap-2">
            @can('update', $member)
                <a href="{{ route('members.edit', $member) }}" class="btn-secondary">
                    <x-icon name="edit" class="h-4 w-4" />
                    Ubah Data
                </a>
            @endcan
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-brand-800 px-6 py-7 text-white sm:px-8">
            <div class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full bg-brand-400/10 blur-3xl"></div>
            <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center">
                <x-avatar :name="$member->full_name" :src="$member->photo_url" size="h-20 w-20" text="text-2xl" class="ring-2 ring-white/30" />
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="truncate text-2xl font-extrabold tracking-tight sm:text-3xl">{{ $member->full_name }}</h1>
                        @if (auth()->user()->member_id === $member->id)
                            <span class="badge bg-white/15 text-white ring-white/20">Akun Anda</span>
                        @endif
                    </div>
                    <p class="mt-1 text-xs text-slate-300">{{ $member->alias ?: 'Nama panggilan belum diisi' }}</p>
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        <span class="badge bg-white/10 text-white ring-white/20">{{ $member->family_status }}</span>
                        @if ($member->gender)
                            <span class="badge bg-white/10 text-white ring-white/20">{{ $member->gender->label() }}</span>
                        @endif
                        @if ($member->birth_date)
                            <span class="badge bg-white/10 text-white ring-white/20">{{ $member->age }} tahun · {{ $member->age_category }}</span>
                        @endif
                    </div>
                </div>
                @can('delete', $member)
                    <form method="POST" action="{{ route('members.destroy', $member) }}" onsubmit="return confirm('Hapus data {{ $member->full_name }}? Tindakan ini tidak dapat dibatalkan.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-rose-500/20 text-rose-100 ring-1 ring-rose-200/30 transition hover:bg-rose-500/30" title="Hapus data jemaat">
                            <x-icon name="trash" class="h-4 w-4" />
                        </button>
                    </form>
                @endcan
            </div>
        </div>

        <div class="card-pad grid gap-5 sm:grid-cols-3">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500">Nomor KTJ</p>
                <p class="mt-1 font-mono text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $member->ktj_id ?: '—' }}</p>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500">Keluarga</p>
                @if ($member->family)
                    <a href="{{ route('families.show', $member->family) }}" class="mt-1 block font-mono text-xs font-semibold text-brand-700 hover:underline dark:text-brand-300">{{ $member->family->kkj_id }}</a>
                    <p class="truncate text-[10px] text-slate-400 dark:text-slate-500">{{ $member->family->head_name }}</p>
                @else
                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">—</p>
                @endif
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500">Wilayah</p>
                <p class="mt-1 text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $member->family?->district ?: '—' }}</p>
                <p class="text-[10px] text-slate-400 dark:text-slate-500">{{ $member->family?->village ?: '' }}</p>
            </div>
        </div>
    </div>

    @if (! empty($member->data_flags))
        <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50/80 px-4 py-3 text-xs text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
            <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
            <div>
                <p class="font-bold">Catatan kualitas data hasil impor</p>
                <p class="mt-0.5 leading-relaxed">{{ implode(' · ', $member->data_flags) }}. Silakan lengkapi melalui tombol Ubah Data.</p>
            </div>
        </div>
    @endif

    <div class="grid gap-5 lg:grid-cols-2">
        <div class="card card-pad">
            <div class="flex items-center gap-2">
                <span class="stat-icon bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><x-icon name="id-card" class="h-4 w-4" /></span>
                <h2 class="section-title">Identitas</h2>
            </div>
            <dl class="mt-5 space-y-3.5 text-sm">
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs text-slate-500 dark:text-slate-400">Nama lengkap</dt>
                    <dd class="text-right text-xs font-semibold text-slate-900 dark:text-white">{{ $member->full_name }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs text-slate-500 dark:text-slate-400">Nama panggilan</dt>
                    <dd class="text-right text-xs font-semibold text-slate-900 dark:text-white">{{ $member->alias ?: '—' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs text-slate-500 dark:text-slate-400">NIK</dt>
                    <dd class="text-right font-mono text-xs font-semibold text-slate-900 dark:text-white">{{ $member->nik ?: '—' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs text-slate-500 dark:text-slate-400">Jenis kelamin</dt>
                    <dd class="text-right text-xs font-semibold text-slate-900 dark:text-white">{{ $member->gender?->label() ?? '—' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs text-slate-500 dark:text-slate-400">Tanggal lahir</dt>
                    <dd class="text-right text-xs font-semibold text-slate-900 dark:text-white">{{ $member->birth_date?->translatedFormat('d F Y') ?? '—' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs text-slate-500 dark:text-slate-400">Status pernikahan</dt>
                    <dd class="text-right text-xs font-semibold text-slate-900 dark:text-white">{{ $member->marital_status ?: '—' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs text-slate-500 dark:text-slate-400">Nama pasangan</dt>
                    <dd class="text-right text-xs font-semibold text-slate-900 dark:text-white">{{ $member->spouse_name ?: '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="card card-pad">
            <div class="flex items-center gap-2">
                <span class="stat-icon bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><x-icon name="phone" class="h-4 w-4" /></span>
                <h2 class="section-title">Kontak</h2>
            </div>
            <dl class="mt-5 space-y-3.5 text-sm">
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs text-slate-500 dark:text-slate-400">Telepon</dt>
                    <dd class="text-right text-xs font-semibold text-slate-900 dark:text-white">{{ $member->phone ?: '—' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs text-slate-500 dark:text-slate-400">WhatsApp</dt>
                    <dd class="text-right text-xs font-semibold text-slate-900 dark:text-white">{{ $member->whatsapp ?: '—' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs text-slate-500 dark:text-slate-400">Email</dt>
                    <dd class="max-w-[65%] break-all text-right text-xs font-semibold text-slate-900 dark:text-white">{{ $member->email ?: '—' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs text-slate-500 dark:text-slate-400">Alamat keluarga</dt>
                    <dd class="max-w-[65%] text-right text-xs font-semibold leading-relaxed text-slate-900 dark:text-white">{{ $member->family?->full_address ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="card card-pad">
            <div class="flex items-center gap-2">
                <span class="stat-icon bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><x-icon name="shield" class="h-4 w-4" /></span>
                <h2 class="section-title">Pelayanan</h2>
            </div>
            <dl class="mt-5 space-y-3.5 text-sm">
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs text-slate-500 dark:text-slate-400">Pelayanan</dt>
                    <dd class="text-right text-xs font-semibold text-slate-900 dark:text-white">{{ $member->ministry ?: '—' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs text-slate-500 dark:text-slate-400">Komsel</dt>
                    <dd class="text-right text-xs font-semibold text-slate-900 dark:text-white">{{ $member->cell_group ?: '—' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs text-slate-500 dark:text-slate-400">Anak ke-</dt>
                    <dd class="text-right text-xs font-semibold text-slate-900 dark:text-white">{{ $member->child_order ?: '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="card card-pad">
            <div class="flex items-center gap-2">
                <span class="stat-icon bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><x-icon name="document" class="h-4 w-4" /></span>
                <h2 class="section-title">Catatan</h2>
            </div>
            <p class="mt-5 whitespace-pre-line text-xs leading-relaxed text-slate-600 dark:text-slate-400">{{ $member->notes ?: 'Belum ada catatan.' }}</p>
        </div>
    </div>
@endsection

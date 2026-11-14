@extends('layouts.app')

@section('title', 'Data KKJ')
@section('eyebrow', 'Data keluarga')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="page-title">Data KKJ</h1>
                <span class="badge bg-slate-100 text-slate-600 ring-slate-500/20 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-600/40">{{ number_format($families->total(), 0, ',', '.') }}</span>
            </div>
            <p class="page-subtitle">Daftar Kartu Keluarga Jemaat beserta alamat dan anggotanya.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('create', \App\Models\Family::class)
                <a href="{{ route('families.create') }}" class="btn-primary">
                    <x-icon name="plus" class="h-4 w-4" />
                    Tambah KKJ
                </a>
            @endcan
        </div>
    </div>

    <form method="GET" action="{{ route('families.index') }}" class="filter-bar">
        <div class="relative flex-1">
            <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input type="search"
                   name="q"
                   value="{{ $filters['q'] ?? '' }}"
                   placeholder="Cari nomor KKJ, kepala keluarga, atau alamat…"
                   class="input pl-10">
        </div>

        <select name="district" class="input sm:w-52">
            <option value="">Semua kecamatan</option>
            @foreach ($districts as $district)
                <option value="{{ $district }}" @selected(($filters['district'] ?? '') === $district)>{{ $district }}</option>
            @endforeach
        </select>

        <div class="flex gap-2">
            <button type="submit" class="btn-primary">
                <x-icon name="filter" class="h-4 w-4" />
                Terapkan
            </button>
            @if (! empty($filters['q']) || ! empty($filters['district']))
                <a href="{{ route('families.index') }}" class="btn-secondary">Reset</a>
            @endif
        </div>
    </form>

    <div class="card overflow-hidden">
        <div class="table-shell hidden md:block">
            <table class="table-base">
                <thead>
                    <tr>
                        <th class="th">Kepala Keluarga</th>
                        <th class="th">Nomor KKJ</th>
                        <th class="th">Alamat</th>
                        <th class="th text-center">Anggota</th>
                        <th class="th text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($families as $family)
                        <tr class="table-row">
                            <td class="td">
                                <div class="flex items-center gap-3">
                                    <x-avatar :name="$family->head_name" :src="$family->photo_url" size="h-10 w-10" text="text-xs" />
                                    <div class="min-w-0">
                                        <a href="{{ route('families.show', $family) }}" class="block truncate font-semibold text-slate-900 hover:text-brand-700 dark:text-white dark:hover:text-brand-300">{{ $family->head_name }}</a>
                                        <p class="truncate text-[11px] text-slate-500 dark:text-slate-400">Masuk {{ $family->joined_year ?: '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="td"><span class="font-mono text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $family->kkj_id }}</span></td>
                            <td class="td">
                                <p class="max-w-xs truncate text-xs text-slate-600 dark:text-slate-400">{{ $family->full_address }}</p>
                            </td>
                            <td class="td text-center">
                                <span class="badge bg-brand-50 text-brand-700 ring-brand-600/20 dark:bg-brand-500/10 dark:text-brand-300 dark:ring-brand-500/30">{{ $family->members_count }} orang</span>
                            </td>
                            <td class="td">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('families.card.download', $family) }}" class="icon-btn" title="Unduh kartu PDF" aria-label="Unduh kartu PDF {{ $family->kkj_id }}">
                                        <x-icon name="download" class="h-4 w-4" />
                                    </a>
                                    <a href="{{ route('families.show', $family) }}" class="icon-btn" title="Lihat detail" aria-label="Lihat detail {{ $family->kkj_id }}">
                                        <x-icon name="eye" class="h-4 w-4" />
                                    </a>
                                    @can('update', $family)
                                        <a href="{{ route('families.edit', $family) }}" class="icon-btn" title="Ubah" aria-label="Ubah {{ $family->kkj_id }}">
                                            <x-icon name="edit" class="h-4 w-4" />
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state title="Tidak ada KKJ ditemukan" description="Coba ubah kata kunci atau filter wilayah, atau tambahkan data keluarga baru." icon="family">
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

        <div class="divide-y divide-slate-100 dark:divide-slate-800 md:hidden">
            @forelse ($families as $family)
                <a href="{{ route('families.show', $family) }}" class="block px-5 py-4 transition hover:bg-slate-50 dark:hover:bg-slate-800/40">
                    <div class="flex items-start gap-3">
                        <x-avatar :name="$family->head_name" :src="$family->photo_url" size="h-11 w-11" text="text-xs" />
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ $family->head_name }}</p>
                                <span class="badge shrink-0 bg-brand-50 text-brand-700 ring-brand-600/20 dark:bg-brand-500/10 dark:text-brand-300 dark:ring-brand-500/30">{{ $family->members_count }} orang</span>
                            </div>
                            <p class="mt-0.5 font-mono text-[11px] font-semibold text-slate-500 dark:text-slate-400">{{ $family->kkj_id }}</p>
                            <p class="mt-1.5 line-clamp-2 text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">{{ $family->full_address }}</p>
                        </div>
                    </div>
                </a>
            @empty
                <x-empty-state title="Tidak ada KKJ ditemukan" description="Coba ubah kata kunci atau filter wilayah." icon="family" />
            @endforelse
        </div>

        <x-pagination :paginator="$families" />
    </div>
@endsection

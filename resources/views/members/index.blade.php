@extends('layouts.app')

@section('title', 'Data Jemaat')
@section('eyebrow', 'Data jemaat')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="page-title">Data Jemaat</h1>
                <span class="badge bg-slate-100 text-slate-600 ring-slate-500/20 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-600/40">{{ number_format($members->total(), 0, ',', '.') }}</span>
            </div>
            <p class="page-subtitle">Data per orang, terhubung ke Kartu Keluarga Jemaat (KKJ).</p>
        </div>
        @can('create', \App\Models\Member::class)
            <a href="{{ route('members.create') }}" class="btn-primary">
                <x-icon name="plus" class="h-4 w-4" />
                Tambah Jemaat
            </a>
        @endcan
    </div>

    <form method="GET" action="{{ route('members.index') }}" class="filter-bar">
        <div class="relative flex-1">
            <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input type="search"
                   name="q"
                   value="{{ $filters['q'] ?? '' }}"
                   placeholder="Cari nama, NIK, nomor KKJ, atau telepon…"
                   class="input pl-10">
        </div>

        <select name="status" class="input sm:w-44">
            <option value="">Semua status</option>
            @foreach (\App\Models\Member::FAMILY_STATUSES as $status)
                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
            @endforeach
        </select>

        <select name="gender" class="input sm:w-36">
            <option value="">Gender</option>
            @foreach (\App\Enums\Gender::options() as $value => $label)
                <option value="{{ $value }}" @selected(($filters['gender'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <select name="age" class="input sm:w-40">
            <option value="">Semua usia</option>
            @foreach (\App\Support\AgeCategory::labels() as $label)
                <option value="{{ $label }}" @selected(($filters['age'] ?? '') === $label)>{{ $label }}</option>
            @endforeach
        </select>

        <div class="flex gap-2">
            <button type="submit" class="btn-primary">
                <x-icon name="filter" class="h-4 w-4" />
                Filter
            </button>
            @if (array_filter($filters))
                <a href="{{ route('members.index') }}" class="btn-secondary">Reset</a>
            @endif
        </div>
    </form>

    <div class="card overflow-hidden">
        <div class="table-shell hidden md:block">
            <table class="table-base">
                <thead>
                    <tr>
                        <th class="th">Nama</th>
                        <th class="th">Keluarga</th>
                        <th class="th">Status</th>
                        <th class="th">Gender</th>
                        <th class="th">Usia</th>
                        <th class="th">Pernikahan</th>
                        <th class="th text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($members as $member)
                        <tr class="table-row">
                            <td class="td">
                                <div class="flex items-center gap-3">
                                    <x-avatar :name="$member->full_name" :src="$member->photo_url" size="h-9 w-9" text="text-[10px]" />
                                    <div class="min-w-0">
                                        <a href="{{ route('members.show', $member) }}" class="block truncate font-semibold text-slate-900 hover:text-brand-700 dark:text-white dark:hover:text-brand-300">{{ $member->full_name }}</a>
                                        <p class="truncate font-mono text-[10px] text-slate-400 dark:text-slate-500">{{ $member->ktj_id ?: '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="td">
                                <a href="{{ route('families.show', $member->family_id) }}" class="block truncate text-xs font-medium text-slate-600 hover:text-brand-700 dark:text-slate-400 dark:hover:text-brand-300">
                                    {{ $member->family?->kkj_id }}
                                </a>
                                <p class="max-w-[180px] truncate text-[10px] text-slate-400 dark:text-slate-500">{{ $member->family?->head_name }}</p>
                            </td>
                            <td class="td">
                                <span class="badge bg-slate-100 text-slate-600 ring-slate-500/20 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-600/40">{{ $member->family_status }}</span>
                            </td>
                            <td class="td text-xs">{{ $member->gender?->label() ?? '—' }}</td>
                            <td class="td">
                                @if ($member->age !== null)
                                    <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $member->age }} th</span>
                                    <span class="badge ml-1 {{ \App\Support\AgeCategory::badgeClasses($member->age_category) }}">{{ $member->age_category }}</span>
                                @else
                                    <span class="text-xs text-slate-400 dark:text-slate-500">—</span>
                                @endif
                            </td>
                            <td class="td text-xs">{{ $member->marital_status ?: '—' }}</td>
                            <td class="td">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('members.show', $member) }}" class="icon-btn" title="Lihat profil">
                                        <x-icon name="eye" class="h-4 w-4" />
                                    </a>
                                    @can('update', $member)
                                        <a href="{{ route('members.edit', $member) }}" class="icon-btn" title="Ubah data">
                                            <x-icon name="edit" class="h-4 w-4" />
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state title="Tidak ada data jemaat ditemukan" description="Coba ubah kata kunci atau filter, atau tambahkan anggota jemaat baru." icon="users">
                                    @can('create', \App\Models\Member::class)
                                        <a href="{{ route('members.create') }}" class="btn-primary btn-sm">Tambah Jemaat</a>
                                    @endcan
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-800 md:hidden">
            @forelse ($members as $member)
                <a href="{{ route('members.show', $member) }}" class="block px-5 py-4 transition hover:bg-slate-50 dark:hover:bg-slate-800/40">
                    <div class="flex items-start gap-3">
                        <x-avatar :name="$member->full_name" :src="$member->photo_url" size="h-11 w-11" text="text-xs" />
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ $member->full_name }}</p>
                            <p class="truncate text-[11px] text-slate-500 dark:text-slate-400">{{ $member->family?->kkj_id }} · {{ $member->family_status }}</p>
                            <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                @if ($member->gender)
                                    <span class="badge {{ $member->gender->badgeClasses() }}">{{ $member->gender->label() }}</span>
                                @endif
                                @if ($member->birth_date)
                                    <span class="badge {{ \App\Support\AgeCategory::badgeClasses($member->age_category) }}">{{ $member->age }} th · {{ $member->age_category }}</span>
                                @endif
                                <span class="text-[10px] text-slate-400 dark:text-slate-500">{{ $member->marital_status ?: 'Status belum diisi' }}</span>
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <x-empty-state title="Tidak ada data jemaat ditemukan" description="Coba ubah kata kunci atau filter." icon="users" />
            @endforelse
        </div>

        <x-pagination :paginator="$members" />
    </div>
@endsection

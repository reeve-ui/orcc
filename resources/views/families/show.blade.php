@extends('layouts.app')

@section('title', 'Detail KKJ')
@section('eyebrow', 'Data keluarga')

@php
    $missingBirthDate = $members->filter(fn ($item) => $item->birth_date === null)->count();
@endphp

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        @if (auth()->user()->isStaff())
            <a href="{{ route('families.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                <x-icon name="arrow-left" class="h-4 w-4" />
                Kembali ke daftar KKJ
            </a>
        @else
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                <x-icon name="arrow-left" class="h-4 w-4" />
                Kembali ke dashboard
            </a>
        @endif
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('families.card', $family) }}" target="_blank" class="btn-secondary">
                <x-icon name="eye" class="h-4 w-4" />
                Pratinjau Kartu
            </a>
            <a href="{{ route('families.card.download', $family) }}" class="btn-primary">
                <x-icon name="download" class="h-4 w-4" />
                Unduh PDF
            </a>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="relative overflow-hidden bg-gradient-to-br from-brand-900 via-brand-800 to-brand-600 px-6 py-7 text-white sm:px-8">
            <div class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
            <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center">
                @if ($family->photo_url)
                    <img src="{{ $family->photo_url }}" alt="Foto keluarga {{ $family->head_name }}" class="h-20 w-20 rounded-2xl object-cover ring-2 ring-white/30">
                @else
                    <span class="inline-flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-2xl font-extrabold ring-1 ring-white/25">
                        {{ $family->initials }}
                    </span>
                @endif
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-brand-100/80">Kartu Keluarga Jemaat</p>
                    <h1 class="mt-1 truncate text-2xl font-extrabold tracking-tight sm:text-3xl">{{ $family->head_name }}</h1>
                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-brand-100/90">
                        <span class="rounded-full bg-white/10 px-2.5 py-1 font-mono font-semibold ring-1 ring-white/15">{{ $family->kkj_id }}</span>
                        <span>{{ $members->count() }} anggota</span>
                        @if ($family->joined_year)
                            <span>· Masuk {{ $family->joined_year }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex shrink-0 gap-1.5">
                    @can('update', $family)
                        <a href="{{ route('families.edit', $family) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-white/10 text-white ring-1 ring-white/20 transition hover:bg-white/20" title="Ubah KKJ">
                            <x-icon name="edit" class="h-4 w-4" />
                        </a>
                    @endcan
                    @can('delete', $family)
                        <form method="POST" action="{{ route('families.destroy', $family) }}" onsubmit="return confirm('Hapus KKJ {{ $family->kkj_id }} beserta seluruh anggotanya? Tindakan ini tidak dapat dibatalkan.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-rose-500/20 text-rose-100 ring-1 ring-rose-200/30 transition hover:bg-rose-500/30" title="Hapus KKJ">
                                <x-icon name="trash" class="h-4 w-4" />
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>

        <div class="card-pad">
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500">Alamat</p>
                    <p class="mt-1 text-xs leading-relaxed text-slate-700 dark:text-slate-300">{{ $family->full_address }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500">Kelurahan / Kecamatan</p>
                    <p class="mt-1 text-xs leading-relaxed text-slate-700 dark:text-slate-300">
                        {{ $family->village ?: '—' }}<br>
                        {{ $family->district ?: '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500">Kota / Provinsi</p>
                    <p class="mt-1 text-xs leading-relaxed text-slate-700 dark:text-slate-300">
                        {{ $family->city ?: '—' }}<br>
                        {{ $family->province ?: '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500">Kode Pos / No. Sukacita</p>
                    <p class="mt-1 text-xs leading-relaxed text-slate-700 dark:text-slate-300">
                        {{ $family->postal_code ?: '—' }}<br>
                        {{ $family->joy_number ?: '—' }}
                    </p>
                </div>
            </div>

            <div class="mt-5 flex flex-col gap-3 border-t border-slate-100 pt-4 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between"
                 x-data="{
                     copied: false,
                     copy() {
                         navigator.clipboard.writeText(@js($family->verification_url)).then(() => {
                             this.copied = true;
                             setTimeout(() => this.copied = false, 2000);
                         }).catch(() => {});
                     }
                 }">
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500">Tautan verifikasi publik</p>
                    <a href="{{ $family->verification_url }}" target="_blank" rel="noopener"
                       class="mt-0.5 block truncate font-mono text-[11px] text-brand-700 hover:underline dark:text-brand-300">
                        {{ $family->verification_url }}
                    </a>
                    <p class="mt-1 text-[10px] text-slate-400 dark:text-slate-500">Tautan ini juga menjadi isi QR pada kartu KKJ. Bagikan hanya kepada keluarga terkait.</p>
                </div>
                <button type="button" class="btn-secondary btn-sm shrink-0" @click="copy()">
                    <x-icon name="check" class="h-3.5 w-3.5" x-show="copied" x-cloak />
                    <span x-text="copied ? 'Tersalin' : 'Salin tautan'">Salin tautan</span>
                </button>
            </div>
        </div>
    </div>

    @if ($missingBirthDate > 0)
        <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50/80 px-4 py-3 text-xs text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
            <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
            <p><span class="font-bold">{{ $missingBirthDate }} anggota</span> belum memiliki tanggal lahir. Lengkapi agar kategori usia dan kartu KKJ akurat.</p>
        </div>
    @endif

    <div class="card overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h2 class="section-title">Anggota keluarga</h2>
                <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">Diurutkan berdasarkan status dalam keluarga.</p>
            </div>
            @can('create', \App\Models\Member::class)
                <a href="{{ route('members.create', ['family_id' => $family->id]) }}" class="btn-primary btn-sm">
                    <x-icon name="plus" class="h-3.5 w-3.5" />
                    Tambah Anggota
                </a>
            @endcan
        </div>

        <div class="table-shell">
            <table class="table-base">
                <thead>
                    <tr>
                        <th class="th">Nama</th>
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
                                        @can('view', $member)
                                            <a href="{{ route('members.show', $member) }}" class="block truncate font-semibold text-slate-900 hover:text-brand-700 dark:text-white dark:hover:text-brand-300">{{ $member->full_name }}</a>
                                        @else
                                            <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $member->full_name }}</p>
                                        @endcan
                                        <p class="truncate font-mono text-[10px] text-slate-400 dark:text-slate-500">{{ $member->ktj_id ?: '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="td">
                                <span class="badge bg-slate-100 text-slate-600 ring-slate-500/20 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-600/40">{{ $member->family_status }}</span>
                            </td>
                            <td class="td text-xs">{{ $member->gender?->label() ?? '—' }}</td>
                            <td class="td text-xs">
                                @if ($member->age !== null)
                                    {{ $member->age }} th
                                @else
                                    <span class="text-slate-400 dark:text-slate-500">—</span>
                                @endif
                            </td>
                            <td class="td text-xs">{{ $member->marital_status ?: '—' }}</td>
                            <td class="td">
                                <div class="flex items-center justify-end gap-1">
                                    @can('view', $member)
                                        <a href="{{ route('members.show', $member) }}" class="icon-btn" title="Lihat profil">
                                            <x-icon name="eye" class="h-4 w-4" />
                                        </a>
                                    @endcan
                                    @can('update', $member)
                                        <a href="{{ route('members.edit', $member) }}" class="icon-btn" title="Ubah data">
                                            <x-icon name="edit" class="h-4 w-4" />
                                        </a>
                                    @endcan
                                    @can('delete', $member)
                                        <form method="POST" action="{{ route('members.destroy', $member) }}" onsubmit="return confirm('Hapus data {{ $member->full_name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="icon-btn text-rose-500 hover:bg-rose-50 hover:text-rose-700 dark:hover:bg-rose-500/10" title="Hapus anggota">
                                                <x-icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state title="Belum ada anggota keluarga" description="Tambahkan anggota keluarga pertama untuk KKJ ini." icon="users" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

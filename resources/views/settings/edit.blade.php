@extends('layouts.app')

@section('title', 'Pengaturan Gereja')
@section('eyebrow', 'Administrasi')

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="page-title">Pengaturan Gereja</h1>
            <p class="page-subtitle">Identitas gereja, logo, kode penomoran KKJ, impor data Excel, dan reset data. Perubahan berlaku untuk tampilan aplikasi dan kartu PDF.</p>
        </div>
        <span class="badge shrink-0 bg-brand-50 text-brand-700 ring-brand-600/20 dark:bg-brand-500/10 dark:text-brand-300 dark:ring-brand-500/30">
            Versi {{ config('app.version') }}
        </span>
    </div>

    @if (! $settings->isConfigured())
        <div class="flex items-start gap-3 rounded-2xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-900 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-100">
            <x-icon name="settings" class="mt-0.5 h-5 w-5 shrink-0" />
            <div>
                <p class="font-semibold">Langkah awal penggunaan aplikasi</p>
                <p class="mt-1 text-xs leading-relaxed opacity-80">Isi kode gereja 5 karakter, nama gereja, dan alamat. Kode gereja dipakai sebagai awalan nomor KKJ baru, misalnya <span class="font-mono font-bold">SEKRT</span> menjadi <span class="font-mono font-bold">SEKRT00260001</span>.</p>
            </div>
        </div>
    @endif

    @if (session('import_result'))
        @php($result = session('import_result'))
        <div class="card overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 dark:border-slate-800 sm:flex-row sm:items-start sm:justify-between sm:px-6">
                <div>
                    <h2 class="section-title">Hasil impor Excel</h2>
                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                        {{ $result['kkj_rows'] }} baris KKJ dan {{ $result['member_rows'] }} baris jemaat dibaca dari file.
                    </p>
                </div>
                @if ($result['dry_run'])
                    <span class="badge bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/30">Uji coba — tidak disimpan</span>
                @elseif ($result['fresh'])
                    <span class="badge bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/30">Impor fresh — data lama dihapus</span>
                @else
                    <span class="badge bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/30">Tersimpan</span>
                @endif
            </div>
            <div class="card-pad space-y-5">
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 dark:border-slate-700 dark:bg-slate-900/60">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">KKJ baru</p>
                        <p class="mt-1 text-xl font-extrabold text-slate-900 dark:text-white">{{ $result['families_created'] }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 dark:border-slate-700 dark:bg-slate-900/60">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">KKJ diperbarui</p>
                        <p class="mt-1 text-xl font-extrabold text-slate-900 dark:text-white">{{ $result['families_updated'] }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 dark:border-slate-700 dark:bg-slate-900/60">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">Jemaat baru</p>
                        <p class="mt-1 text-xl font-extrabold text-slate-900 dark:text-white">{{ $result['members_created'] }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 dark:border-slate-700 dark:bg-slate-900/60">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">Jemaat diperbarui</p>
                        <p class="mt-1 text-xl font-extrabold text-slate-900 dark:text-white">{{ $result['members_updated'] }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 dark:border-slate-700 dark:bg-slate-900/60">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">Dilewati</p>
                        <p class="mt-1 text-xl font-extrabold text-slate-900 dark:text-white">{{ $result['skipped'] }}</p>
                    </div>
                </div>

                @if (! empty($result['flags']))
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 dark:border-amber-500/30 dark:bg-amber-500/10">
                        <p class="text-xs font-bold text-amber-900 dark:text-amber-200">Catatan kualitas data</p>
                        <ul class="mt-2 space-y-1 text-xs text-amber-800 dark:text-amber-200/90">
                            @foreach ($result['flags'] as $flag => $count)
                                <li class="flex items-center justify-between gap-3">
                                    <span>{{ $flag }}</span>
                                    <span class="font-mono font-bold">{{ $count }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <p class="mt-2 text-[11px] leading-relaxed text-amber-700 dark:text-amber-200/80">Nilai yang tidak valid dikosongkan dan ditandai pada anggota baru. Lengkapi datanya lewat menu jemaat.</p>
                    </div>
                @endif

                <p class="hint">
                    @if ($result['dry_run'])
                        Mode uji coba: seluruh perubahan dibatalkan dan database tidak berubah.
                    @elseif ($result['fresh'])
                        Data KKJ dan jemaat lama sudah dihapus, lalu diganti dengan data dari file.
                    @else
                        Impor non-destruktif: data yang sudah terisi tidak ditimpa. Jalankan ulang dengan aman untuk melengkapi kolom yang masih kosong.
                    @endif
                </p>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800 sm:px-6">
                <h2 class="section-title">Identitas gereja</h2>
                <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Tampil di sidebar, halaman verifikasi, dan kop kartu KKJ.</p>
            </div>
            <div class="card-pad grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="label">Nama Gereja <span class="text-rose-500">*</span></label>
                    <input id="name" name="name" type="text" value="{{ old('name', $settings->name) }}" required
                           class="input @error('name') input-error @enderror">
                    @error('name') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="tagline" class="label">Tagline / Subjudul</label>
                    <input id="tagline" name="tagline" type="text" value="{{ old('tagline', $settings->tagline) }}"
                           placeholder="Data Jemaat"
                           class="input @error('tagline') input-error @enderror">
                    @error('tagline') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="address" class="label">Alamat Gereja <span class="text-rose-500">*</span></label>
                    <input id="address" name="address" type="text" value="{{ old('address', $settings->address) }}" required
                           placeholder="Kota, provinsi"
                           class="input @error('address') input-error @enderror">
                    @error('address') <p class="error-text">{{ $message }}</p> @enderror
                    <p class="hint">Dipakai juga sebagai kota penerbitan pada tanda tangan kartu.</p>
                </div>

                <div>
                    <label for="phone" class="label">Telepon</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $settings->phone) }}"
                           class="input @error('phone') input-error @enderror">
                    @error('phone') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="label">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $settings->email) }}"
                           class="input @error('email') input-error @enderror">
                    @error('email') <p class="error-text">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="card">
            <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800 sm:px-6">
                <h2 class="section-title">Kode gereja & penomoran KKJ</h2>
                <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Kode 5 karakter menjadi awalan nomor KKJ baru. Nomor lama yang sudah ada tidak diubah.</p>
            </div>
            <div class="card-pad grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="church_code" class="label">Kode Gereja <span class="text-rose-500">*</span></label>
                    <input id="church_code" name="church_code" type="text" maxlength="5"
                           value="{{ old('church_code', $settings->church_code) }}" required
                           @readonly($settings->codeIsLocked())
                           class="input font-mono uppercase tracking-[0.25em] @error('church_code') input-error @enderror @if ($settings->codeIsLocked()) cursor-not-allowed bg-slate-50 text-slate-500 dark:bg-slate-900/60 dark:text-slate-400 @endif">
                    @error('church_code') <p class="error-text">{{ $message }}</p> @enderror
                    @if ($settings->codeIsLocked())
                        <p class="hint">Kode sudah dipakai pada nomor KKJ dan tidak dapat diubah.</p>
                    @elseif ($settings->isConfigured())
                        <p class="hint">Kode dapat diubah selama belum dipakai pada nomor KKJ baru.</p>
                    @else
                        <p class="hint">Contoh: SEKRT. Hanya huruf dan angka, tepat 5 karakter.</p>
                    @endif
                </div>

                <div>
                    <p class="label">Format nomor KKJ baru</p>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-mono text-sm font-bold tracking-wider text-slate-700 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-200">
                        {{ strtoupper(old('church_code', $settings->church_code) ?: 'SEKRT') }}00{{ now()->format('y') }}<span class="text-brand-600 dark:text-brand-400">0001</span>
                    </div>
                    <p class="hint">5 karakter kode + 00 + 2 digit tahun + 4 digit urutan. Urutan kembali ke 0001 setiap awal tahun.</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800 sm:px-6">
                <h2 class="section-title">Logo gereja</h2>
                <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Opsional. Logo dipakai di sidebar, halaman verifikasi, dan kop kartu KKJ. Format JPG atau PNG, maksimal 2 MB. Logo otomatis diperkecil menjadi maksimal 512 × 512 px.</p>
            </div>
            <div class="card-pad flex flex-col gap-5 sm:flex-row sm:items-center">
                <x-church-logo class="h-20 w-20" icon-class="h-8 w-8" :src="$settings->logo_url" />

                <div class="flex-1 space-y-3">
                    <input type="file" name="logo" accept="image/jpeg,image/png"
                           class="block w-full text-xs text-slate-500 file:mr-3 file:rounded-xl file:border-0 file:bg-brand-50 file:px-4 file:py-2.5 file:text-xs file:font-bold file:text-brand-700 hover:file:bg-brand-100 dark:text-slate-400 dark:file:bg-slate-800 dark:file:text-slate-200">
                    @error('logo') <p class="error-text">{{ $message }}</p> @enderror

                    @if ($settings->logo_url)
                        <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300">
                            <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500 dark:border-slate-600 dark:bg-slate-900">
                            Hapus logo saat ini
                        </label>
                        <p class="hint">Tanpa logo, aplikasi dan kartu memakai monogram salib.</p>
                    @else
                        <p class="hint">Belum ada logo. Aplikasi memakai monogram salib.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="card">
            <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800 sm:px-6">
                <h2 class="section-title">Catatan & penanda tangan kartu KKJ</h2>
                <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Teks tambahan dan identitas penanda tangan pada bagian bawah kartu PDF.</p>
            </div>
            <div class="card-pad grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="card_note" class="label">Catatan Kartu</label>
                    <textarea id="card_note" name="card_note" rows="3" class="input resize-y @error('card_note') input-error @enderror">{{ old('card_note', $settings->card_note) }}</textarea>
                    @error('card_note') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="card_signature_name" class="label">Nama Penanda Tangan</label>
                    <input id="card_signature_name" name="card_signature_name" type="text" value="{{ old('card_signature_name', $settings->card_signature_name) }}"
                           placeholder="Pdt. Nama, S.Th."
                           class="input @error('card_signature_name') input-error @enderror">
                    @error('card_signature_name') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="card_signature_title" class="label">Jabatan Penanda Tangan</label>
                    <input id="card_signature_title" name="card_signature_title" type="text" value="{{ old('card_signature_title', $settings->card_signature_title) }}"
                           placeholder="Gembala Sidang"
                           class="input @error('card_signature_title') input-error @enderror">
                    @error('card_signature_title') <p class="error-text">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <a href="{{ route('dashboard') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">
                <x-icon name="check" class="h-4 w-4" />
                Simpan Pengaturan
            </button>
        </div>
    </form>

    <div class="card" x-data="{ fresh: {{ old('fresh') ? 'true' : 'false' }} }">
        <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 dark:border-slate-800 sm:flex-row sm:items-start sm:justify-between sm:px-6">
            <div>
                <h2 class="section-title">Impor data dari Excel</h2>
                <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Unggah workbook ORCCDATA untuk menambah atau melengkapi data KKJ dan jemaat. Impor hanya dapat dilakukan admin.</p>
            </div>
            <a href="{{ route('settings.import.template') }}" class="btn-secondary btn-sm shrink-0">
                <x-icon name="download" class="h-4 w-4" />
                Unduh template
            </a>
        </div>

        <form method="POST" action="{{ route('settings.import') }}" enctype="multipart/form-data" class="card-pad space-y-5"
              @submit="if (fresh && !confirm('Impor fresh akan menghapus seluruh data KKJ dan jemaat. Lanjutkan?')) $event.preventDefault()">
            @csrf

            <div>
                <label for="excel" class="label">File Excel <span class="text-rose-500">*</span></label>
                <input id="excel" name="excel" type="file" accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" required
                       class="block w-full text-xs text-slate-500 file:mr-3 file:rounded-xl file:border-0 file:bg-brand-50 file:px-4 file:py-2.5 file:text-xs file:font-bold file:text-brand-700 hover:file:bg-brand-100 dark:text-slate-400 dark:file:bg-slate-800 dark:file:text-slate-200">
                @error('excel') <p class="error-text">{{ $message }}</p> @enderror
                <p class="hint">Format <span class="font-mono">.xlsx</span> atau <span class="font-mono">.xls</span>, maksimal 20 MB. Sheet wajib: <span class="font-mono">KKJ</span> dan <span class="font-mono">JEMAAT</span>.</p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 px-4 py-3 transition hover:border-brand-300 hover:bg-brand-50/40 dark:border-slate-700 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/5">
                    <input type="checkbox" name="dry_run" value="1" @checked(old('dry_run'))
                           class="mt-0.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500 dark:border-slate-600 dark:bg-slate-900">
                    <span>
                        <span class="block text-xs font-bold text-slate-800 dark:text-slate-100">Uji coba (dry-run)</span>
                        <span class="mt-0.5 block text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">Proses file dan tampilkan hasilnya tanpa menyimpan perubahan.</span>
                    </span>
                </label>

                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 px-4 py-3 transition hover:border-rose-300 hover:bg-rose-50/40 dark:border-slate-700 dark:hover:border-rose-500/40 dark:hover:bg-rose-500/5">
                    <input type="checkbox" name="fresh" value="1" x-model="fresh" @checked(old('fresh'))
                           class="mt-0.5 rounded border-slate-300 text-rose-600 focus:ring-rose-500 dark:border-slate-600 dark:bg-slate-900">
                    <span>
                        <span class="block text-xs font-bold text-slate-800 dark:text-slate-100">Impor fresh</span>
                        <span class="mt-0.5 block text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">Hapus seluruh data KKJ dan jemaat sebelum mengimpor ulang dari file.</span>
                    </span>
                </label>
            </div>

            <div x-show="fresh" x-cloak class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 dark:border-rose-500/30 dark:bg-rose-500/10">
                <label class="flex cursor-pointer items-start gap-3">
                    <input type="checkbox" name="confirm_fresh" value="1" @checked(old('confirm_fresh'))
                           class="mt-0.5 rounded border-rose-300 text-rose-600 focus:ring-rose-500 dark:border-rose-500/50 dark:bg-slate-900">
                    <span class="text-xs leading-relaxed text-rose-800 dark:text-rose-200">
                        Saya memahami bahwa seluruh data KKJ dan jemaat yang ada akan dihapus sebelum impor. Tindakan ini tidak dapat dibatalkan.
                    </span>
                </label>
                @error('confirm_fresh') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-[11px] leading-relaxed text-slate-600 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300">
                <p class="font-bold text-slate-800 dark:text-slate-100">Format yang dibutuhkan</p>
                <ul class="mt-2 list-inside list-disc space-y-1">
                    <li>Sheet <span class="font-mono font-semibold">KKJ</span> — satu baris per keluarga; kolom <span class="font-mono">KKJID</span> wajib.</li>
                    <li>Sheet <span class="font-mono font-semibold">JEMAAT</span> — satu baris per anggota; kolom <span class="font-mono">KKJID</span> dan <span class="font-mono">NAMA LENGKAP</span> wajib.</li>
                    <li>Impor aman diulang: KKJ dicocokkan lewat <span class="font-mono">KKJID</span>, anggota lewat <span class="font-mono">KTJID</span>.</li>
                    <li>Data yang sudah terisi tidak ditimpa; nilai tidak valid dikosongkan dan ditandai.</li>
                </ul>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-primary">
                    <x-icon name="document" class="h-4 w-4" />
                    Impor Data
                </button>
            </div>
        </form>
    </div>

    <div class="card border-rose-200 dark:border-rose-500/30"
         x-data="{
             confirming: {{ $errors->has('confirmation') || $errors->has('confirm_reset') ? 'true' : 'false' }},
             confirmation: @js(old('confirmation', '')),
             acknowledged: {{ old('confirm_reset') ? 'true' : 'false' }},
         }">
        <div class="flex flex-col gap-3 border-b border-rose-100 px-5 py-4 dark:border-rose-500/20 sm:flex-row sm:items-start sm:justify-between sm:px-6">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-300">
                    <x-icon name="trash" class="h-5 w-5" />
                </span>
                <div>
                    <h2 class="section-title text-rose-700 dark:text-rose-300">Reset data KKJ &amp; jemaat</h2>
                    <p class="mt-1 text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">Menghapus seluruh data KKJ dan jemaat secara permanen. Gunakan hanya saat memulai data dari awal.</p>
                </div>
            </div>
            <span class="badge shrink-0 bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/30">Khusus admin</span>
        </div>

        <div class="card-pad space-y-4">
            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-900/60">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">KKJ tersimpan</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($familyCount, 0, ',', '.') }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-900/60">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Jemaat tersimpan</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($memberCount, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs leading-relaxed text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">
                <p class="font-bold">Tindakan ini akan menghapus:</p>
                <ul class="mt-2 list-inside list-disc space-y-1">
                    <li>Seluruh data KKJ dan seluruh data jemaat.</li>
                    <li>Foto keluarga dan foto jemaat yang tersimpan.</li>
                    <li>Urutan nomor KKJ, sehingga penomoran dimulai kembali dari awal.</li>
                </ul>
                <p class="mt-2 font-semibold">Akun pengguna, pengaturan gereja, dan data wilayah tetap dipertahankan. Tindakan ini tidak dapat dibatalkan.</p>
            </div>

            <div x-show="!confirming">
                <button type="button" class="btn-danger" @click="confirming = true">
                    <x-icon name="trash" class="h-4 w-4" />
                    Reset data
                </button>
            </div>

            <form x-show="confirming" x-cloak method="POST" action="{{ route('settings.reset-data') }}"
                  class="space-y-4 rounded-xl border border-rose-300 bg-white px-4 py-4 dark:border-rose-500/40 dark:bg-slate-950/40">
                @csrf
                @method('DELETE')

                <div>
                    <label for="confirmation" class="label">Ketik <span class="font-mono font-bold text-rose-600 dark:text-rose-300">RESET DATA</span> untuk melanjutkan</label>
                    <input id="confirmation" name="confirmation" type="text" x-model="confirmation" value="{{ old('confirmation') }}"
                           @input="confirmation = confirmation.toUpperCase()"
                           autocomplete="off" spellcheck="false" placeholder="RESET DATA"
                           class="input @error('confirmation') input-error @enderror">
                    @error('confirmation') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-rose-200 bg-rose-50/60 px-4 py-3 dark:border-rose-500/30 dark:bg-rose-500/5">
                    <input type="checkbox" name="confirm_reset" value="1" x-model="acknowledged" @checked(old('confirm_reset'))
                           class="mt-0.5 rounded border-rose-300 text-rose-600 focus:ring-rose-500 dark:border-rose-500/50 dark:bg-slate-900">
                    <span class="text-xs leading-relaxed text-rose-800 dark:text-rose-200">
                        Saya memahami bahwa seluruh data KKJ, jemaat, dan foto terkait akan dihapus permanen dan tidak dapat dibatalkan.
                    </span>
                </label>
                @error('confirm_reset') <p class="error-text">{{ $message }}</p> @enderror

                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button" class="btn-secondary" @click="confirming = false; confirmation = ''; acknowledged = false">Batal</button>
                    <button type="submit" class="btn-danger" :disabled="confirmation !== 'RESET DATA' || !acknowledged">
                        <x-icon name="trash" class="h-4 w-4" />
                        Hapus semua data
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

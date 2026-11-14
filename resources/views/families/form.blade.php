@extends('layouts.app')

@section('title', $family->exists ? 'Ubah KKJ' : 'Tambah KKJ')
@section('eyebrow', 'Data keluarga')

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ $family->exists ? route('families.show', $family) : route('families.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
            <x-icon name="arrow-left" class="h-4 w-4" />
            {{ $family->exists ? 'Kembali ke detail KKJ' : 'Kembali ke daftar KKJ' }}
        </a>
    </div>

    <div>
        <h1 class="page-title">{{ $family->exists ? 'Ubah Data KKJ' : 'Tambah Data KKJ' }}</h1>
        <p class="page-subtitle">{{ $family->exists ? 'Perbarui identitas, alamat, atau foto keluarga.' : 'Isi identitas keluarga. Nomor KKJ dibuat otomatis oleh sistem.' }}</p>
    </div>

    <form method="POST"
          action="{{ $family->exists ? route('families.update', $family) : route('families.store') }}"
          enctype="multipart/form-data"
          class="space-y-5">
        @csrf
        @if ($family->exists)
            @method('PUT')
        @endif

        <div class="card">
            <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800 sm:px-6">
                <h2 class="section-title">Identitas KKJ</h2>
                <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Nomor KKJ dibuat otomatis berdasarkan kode gereja, tahun berjalan, dan urutan keluarga.</p>
            </div>
            <div class="card-pad grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="kkj_display" class="label">Nomor KKJ</label>
                    @if ($family->exists)
                        <input id="kkj_display" type="text" value="{{ $family->kkj_id }}" readonly
                               class="input cursor-not-allowed bg-slate-50 font-mono uppercase text-slate-600 dark:bg-slate-900/60 dark:text-slate-300">
                        <p class="hint">Nomor KKJ tidak dapat diubah setelah dibuat.</p>
                    @else
                        <input id="kkj_display" type="text"
                               value="{{ $kkjPreview }}"
                               readonly
                               class="input cursor-not-allowed bg-slate-50 font-mono uppercase tracking-wider text-slate-500 dark:bg-slate-900/60 dark:text-slate-400">
                        <p class="hint">Dibuat otomatis saat KKJ disimpan. Format: kode gereja + 00 + tahun + urutan 4 angka.</p>
                    @endif
                </div>

                <div>
                    <label for="head_name" class="label">Kepala Keluarga <span class="text-rose-500">*</span></label>
                    <input id="head_name" name="head_name" type="text" value="{{ old('head_name', $family->head_name) }}" required autofocus
                           placeholder="Nama kepala keluarga"
                           class="input @error('head_name') input-error @enderror">
                    @error('head_name') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="joined_year" class="label">Tahun Masuk</label>
                    <input id="joined_year" name="joined_year" type="number" min="1900" max="{{ now()->year + 1 }}" value="{{ old('joined_year', $family->joined_year) }}"
                           placeholder="{{ now()->year }}"
                           class="input @error('joined_year') input-error @enderror">
                    @error('joined_year') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="joy_number" class="label">Nomor Sukacita</label>
                    <input id="joy_number" name="joy_number" type="text" value="{{ old('joy_number', $family->joy_number) }}"
                           placeholder="Opsional"
                           class="input @error('joy_number') input-error @enderror">
                    @error('joy_number') <p class="error-text">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        @if (! $regionsReady)
            <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
                <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
                <div>
                    <p class="font-semibold">Data wilayah belum tersedia.</p>
                    <p class="mt-1 text-xs">Jalankan <code class="rounded bg-amber-100 px-1.5 py-0.5 font-mono dark:bg-amber-500/20">php artisan regions:import</code> agar pilihan provinsi sampai kode pos dapat digunakan.</p>
                </div>
            </div>
        @endif

        <div class="card"
             x-data="addressPicker($el)"
             data-initial="{{ json_encode([
                 'province' => old('province', $family->province),
                 'city' => old('city', $family->city),
                 'district' => old('district', $family->district),
                 'village' => old('village', $family->village),
                 'postal_code' => old('postal_code', $family->postal_code),
             ]) }}"
             data-provinces-url="{{ route('regions.provinces') }}"
             data-cities-url="{{ route('regions.cities') }}"
             data-districts-url="{{ route('regions.districts') }}"
             data-villages-url="{{ route('regions.villages') }}"
             data-postal-codes-url="{{ route('regions.postal-codes') }}">
            <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800 sm:px-6">
                <h2 class="section-title">Alamat tinggal</h2>
                <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Pilih wilayah secara berjenjang. Data dipakai untuk dashboard sebaran dan pengelompokan pelayanan.</p>
                @if ($family->exists)
                    <p class="mt-1 text-[11px] text-amber-600 dark:text-amber-400">Jika alamat lama belum sesuai data wilayah terbaru, pilih ulang provinsi sampai kode pos.</p>
                @endif
            </div>
            <div class="card-pad grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label for="province" class="label">Provinsi <span class="text-rose-500">*</span></label>
                    <select id="province" name="province" required
                            x-model="province"
                            @change="changeProvince()"
                            :disabled="loading.provinces"
                            class="input @error('province') input-error @enderror">
                        <option value="">Pilih provinsi</option>
                        <template x-for="item in provinces" :key="item">
                            <option :value="item" x-text="item"></option>
                        </template>
                    </select>
                    <p class="hint" x-show="loading.provinces" x-cloak>Memuat provinsi…</p>
                    @error('province') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="city" class="label">Kabupaten / Kota <span class="text-rose-500">*</span></label>
                    <select id="city" name="city" required
                            x-model="city"
                            @change="changeCity()"
                            :disabled="!province || loading.cities"
                            class="input @error('city') input-error @enderror">
                        <option value="">Pilih kabupaten/kota</option>
                        <template x-for="item in cities" :key="item">
                            <option :value="item" x-text="item"></option>
                        </template>
                    </select>
                    <p class="hint" x-show="loading.cities" x-cloak>Memuat kabupaten/kota…</p>
                    @error('city') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="district" class="label">Kecamatan <span class="text-rose-500">*</span></label>
                    <select id="district" name="district" required
                            x-model="district"
                            @change="changeDistrict()"
                            :disabled="!city || loading.districts"
                            class="input @error('district') input-error @enderror">
                        <option value="">Pilih kecamatan</option>
                        <template x-for="item in districts" :key="item">
                            <option :value="item" x-text="item"></option>
                        </template>
                    </select>
                    <p class="hint" x-show="loading.districts" x-cloak>Memuat kecamatan…</p>
                    @error('district') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="village" class="label">Kelurahan / Desa <span class="text-rose-500">*</span></label>
                    <select id="village" name="village" required
                            x-model="village"
                            @change="changeVillage()"
                            :disabled="!district || loading.villages"
                            class="input @error('village') input-error @enderror">
                        <option value="">Pilih kelurahan/desa</option>
                        <template x-for="item in villages" :key="item">
                            <option :value="item" x-text="item"></option>
                        </template>
                    </select>
                    <p class="hint" x-show="loading.villages" x-cloak>Memuat kelurahan/desa…</p>
                    @error('village') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="postal_code" class="label">Kode Pos <span class="text-rose-500">*</span></label>
                    <select id="postal_code" name="postal_code" required
                            x-model="postal_code"
                            :disabled="!village || loading.postalCodes"
                            class="input @error('postal_code') input-error @enderror">
                        <option value="">Pilih kode pos</option>
                        <template x-for="item in postalCodes" :key="item">
                            <option :value="item" x-text="item"></option>
                        </template>
                    </select>
                    <p class="hint" x-show="loading.postalCodes" x-cloak>Memuat kode pos…</p>
                    @error('postal_code') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2 lg:col-span-3">
                    <label for="address" class="label">Alamat Lengkap <span class="text-rose-500">*</span></label>
                    <textarea id="address" name="address" rows="3" required placeholder="Jalan, RT/RW, nomor rumah, patokan…" class="input resize-y @error('address') input-error @enderror">{{ old('address', $family->address) }}</textarea>
                    @error('address') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2 lg:col-span-3" x-show="error" x-cloak>
                    <p class="error-text" x-text="error"></p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800 sm:px-6">
                <h2 class="section-title">Foto keluarga</h2>
                <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Opsional. Format JPG, PNG, atau WebP, maksimal 2 MB. Tanpa unggahan, sistem memakai foto default.</p>
            </div>
            <div class="card-pad flex flex-col gap-4 sm:flex-row sm:items-center">
                <x-avatar :name="$family->head_name ?: 'KKJ'" :src="$family->photo_url" size="h-16 w-16" text="text-lg" />
                <div class="flex-1">
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="block w-full text-xs text-slate-500 file:mr-3 file:rounded-xl file:border-0 file:bg-brand-50 file:px-4 file:py-2.5 file:text-xs file:font-bold file:text-brand-700 hover:file:bg-brand-100 dark:text-slate-400 dark:file:bg-slate-800 dark:file:text-slate-200">
                    @error('photo') <p class="error-text">{{ $message }}</p> @enderror
                    @if ($family->has_custom_photo)
                        <p class="hint">Foto saat ini akan diganti jika Anda memilih file baru.</p>
                    @elseif ($family->photo_path)
                        <p class="hint">Referensi foto lama dari Excel tersimpan, tetapi file aslinya tidak ditemukan. Sistem memakai foto default.</p>
                    @else
                        <p class="hint">Belum ada foto khusus. Sistem memakai foto default keluarga.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <a href="{{ $family->exists ? route('families.show', $family) : route('families.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">
                <x-icon name="check" class="h-4 w-4" />
                {{ $family->exists ? 'Simpan Perubahan' : 'Simpan KKJ' }}
            </button>
        </div>
    </form>
@endsection

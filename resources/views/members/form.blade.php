@extends('layouts.app')

@section('title', $member->exists ? 'Ubah Data Jemaat' : 'Tambah Jemaat')
@section('eyebrow', 'Data jemaat')

@php
    $backUrl = $member->exists
        ? route('members.show', $member)
        : ($member->family_id ? route('families.show', $member->family_id) : route('members.index'));

    $initialFamilyId = (string) old('family_id', $member->family_id);
    $initialFamilyStatus = (string) (old('family_status') ?: ($member->family_status ?: \App\Models\Member::FAMILY_STATUS_ANGGOTA));
    $initialChildOrder = (string) old('child_order', $member->child_order);
    $initialGender = (string) (old('gender') ?: ($member->gender?->value ?: \App\Enums\Gender::Pria->value));
    $initialMaritalStatus = (string) (old('marital_status') ?: ($member->marital_status ?: \App\Models\Member::MARITAL_STATUSES[0]));

    $memberFormData = [
        'familyId' => $initialFamilyId,
        'familyStatus' => $initialFamilyStatus,
        'childOrder' => $initialChildOrder,
        'gender' => $initialGender,
        'maritalStatus' => $initialMaritalStatus,
        'existingKtj' => (string) old('ktj_id', $member->ktj_id ?? ''),
        'original' => [
            'familyId' => (string) ($member->family_id ?? ''),
            'familyStatus' => (string) ($member->family_status ?? ''),
            'childOrder' => (string) ($member->child_order ?? ''),
            'gender' => (string) ($member->gender?->value ?? ''),
        ],
    ];

    $minBirthDate = now()->subYears(99)->toDateString();
@endphp

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ $backUrl }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
            <x-icon name="arrow-left" class="h-4 w-4" />
            Kembali
        </a>
    </div>

    <div>
        <h1 class="page-title">{{ $member->exists ? 'Ubah Data Jemaat' : 'Tambah Data Jemaat' }}</h1>
        <p class="page-subtitle">{{ $member->exists ? 'Perbarui data pribadi, kontak, atau pelayanan jemaat.' : 'Data jemaat selalu terhubung ke satu Kartu Keluarga Jemaat.' }}</p>
    </div>

    @if (! empty($member->data_flags))
        <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50/80 px-4 py-3 text-xs text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
            <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
            <p><span class="font-bold">Catatan impor:</span> {{ implode(' · ', $member->data_flags) }}.</p>
        </div>
    @endif

    <form method="POST"
          action="{{ $member->exists ? route('members.update', $member) : route('members.store') }}"
          enctype="multipart/form-data"
          class="space-y-5"
          x-data='memberForm(@json($memberFormData))'>
        @csrf
        @if ($member->exists)
            @method('PUT')
        @endif

        <div class="card">
            <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800 sm:px-6">
                <h2 class="section-title">Keluarga</h2>
                <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Pilih KKJ dan posisi jemaat dalam keluarga.</p>
            </div>
            <div class="card-pad grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div class="sm:col-span-2 lg:col-span-1">
                    <label for="family_id" class="label">Kartu Keluarga Jemaat <span class="text-rose-500">*</span></label>
                    <select id="family_id" name="family_id" required x-model="familyId" class="input @error('family_id') input-error @enderror">
                        <option value="">Pilih keluarga…</option>
                        @foreach ($families as $familyOption)
                            <option value="{{ $familyOption->id }}" data-kkj-id="{{ $familyOption->kkj_id }}" @selected((int) old('family_id', $member->family_id) === $familyOption->id)>
                                {{ $familyOption->kkj_id }} · {{ $familyOption->head_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('family_id') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="family_status" class="label">Status dalam keluarga <span class="text-rose-500">*</span></label>
                    <select id="family_status" name="family_status" required x-model="familyStatus" class="input @error('family_status') input-error @enderror">
                        @foreach (\App\Models\Member::FAMILY_STATUSES as $status)
                            <option value="{{ $status }}" @selected((old('family_status') ?: ($member->family_status ?: \App\Models\Member::FAMILY_STATUS_ANGGOTA)) === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    @error('family_status') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div x-show="isAnggota" x-cloak>
                    <label for="child_order" class="label">Anak ke- <span class="text-rose-500">*</span></label>
                    <input id="child_order" name="child_order" type="number" min="1" max="9" value="{{ old('child_order', $member->child_order) }}"
                           placeholder="Contoh: 1"
                           x-model="childOrder"
                           x-bind:required="isAnggota"
                           x-bind:disabled="!isAnggota"
                           class="input @error('child_order') input-error @enderror">
                    @error('child_order') <p class="error-text">{{ $message }}</p> @enderror
                    <p class="hint">Wajib diisi untuk status Anggota Keluarga (1–9).</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800 sm:px-6">
                <h2 class="section-title">Identitas pribadi</h2>
            </div>
            <div class="card-pad grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div class="sm:col-span-2">
                    <label for="full_name" class="label">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input id="full_name" name="full_name" type="text" value="{{ old('full_name', $member->full_name) }}" required autofocus
                           placeholder="Sesuai dokumen resmi"
                           class="input @error('full_name') input-error @enderror">
                    @error('full_name') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="alias" class="label">Nama Panggilan</label>
                    <input id="alias" name="alias" type="text" value="{{ old('alias', $member->alias) }}" class="input @error('alias') input-error @enderror">
                    @error('alias') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="ktj_id" class="label">Nomor KTJ</label>
                    <input id="ktj_id" name="ktj_id" type="text" value="{{ old('ktj_id', $member->ktj_id) }}" placeholder="SEKRT0026000100"
                           readonly aria-readonly="true" x-bind:value="ktjId"
                           class="input cursor-not-allowed bg-slate-50 font-mono uppercase dark:bg-slate-900/50 @error('ktj_id') input-error @enderror">
                    @error('ktj_id') <p class="error-text">{{ $message }}</p> @enderror
                    <p class="hint">Dibuat otomatis: 13 karakter nomor KKJ + kode status/anak + jenis kelamin (0 pria, 1 wanita).</p>
                </div>

                <div>
                    <label for="nik" class="label">NIK (KTP)</label>
                    <input id="nik" name="nik" type="text" inputmode="numeric" maxlength="16" value="{{ old('nik', $member->nik) }}" placeholder="16 angka"
                           class="input font-mono @error('nik') input-error @enderror">
                    @error('nik') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="gender" class="label">Jenis Kelamin <span class="text-rose-500">*</span></label>
                    <select id="gender" name="gender" required x-model="gender" class="input @error('gender') input-error @enderror">
                        @foreach (\App\Enums\Gender::options() as $value => $label)
                            <option value="{{ $value }}" @selected((old('gender') ?: ($member->gender?->value ?: \App\Enums\Gender::Pria->value)) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('gender') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="birth_date" class="label">Tanggal Lahir <span class="text-rose-500">*</span></label>
                    <input id="birth_date" name="birth_date" type="date" required min="{{ $minBirthDate }}" max="{{ now()->format('Y-m-d') }}"
                           value="{{ old('birth_date', $member->birth_date?->format('Y-m-d')) }}"
                           class="input @error('birth_date') input-error @enderror">
                    @error('birth_date') <p class="error-text">{{ $message }}</p> @enderror
                    <p class="hint">Maksimal hari ini, minimal 99 tahun sebelum hari ini.</p>
                </div>

                <div>
                    <label for="marital_status" class="label">Status Pernikahan <span class="text-rose-500">*</span></label>
                    <select id="marital_status" name="marital_status" required x-model="maritalStatus" class="input @error('marital_status') input-error @enderror">
                        @foreach (\App\Models\Member::MARITAL_STATUSES as $status)
                            <option value="{{ $status }}" @selected((old('marital_status') ?: ($member->marital_status ?: \App\Models\Member::MARITAL_STATUSES[0])) === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    @error('marital_status') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div x-show="isMarried" x-cloak>
                    <label for="spouse_name" class="label">Nama Pasangan</label>
                    <input id="spouse_name" name="spouse_name" type="text" value="{{ old('spouse_name', $member->spouse_name) }}"
                           x-bind:disabled="!isMarried"
                           class="input @error('spouse_name') input-error @enderror">
                    @error('spouse_name') <p class="error-text">{{ $message }}</p> @enderror
                    <p class="hint">Ditampilkan untuk status pernikahan Telah Menikah.</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800 sm:px-6">
                <h2 class="section-title">Kontak</h2>
            </div>
            <div class="card-pad grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label for="phone" class="label">Telepon / Seluler</label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone', $member->phone) }}" placeholder="0812…" class="input @error('phone') input-error @enderror">
                    @error('phone') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="whatsapp" class="label">WhatsApp</label>
                    <input id="whatsapp" name="whatsapp" type="tel" value="{{ old('whatsapp', $member->whatsapp) }}" placeholder="62812…" class="input @error('whatsapp') input-error @enderror">
                    @error('whatsapp') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="label">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $member->email) }}" placeholder="nama@email.com" class="input @error('email') input-error @enderror">
                    @error('email') <p class="error-text">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="card">
            <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800 sm:px-6">
                <h2 class="section-title">Pelayanan & catatan</h2>
            </div>
            <div class="card-pad grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="ministry" class="label">Pelayanan</label>
                    <input id="ministry" name="ministry" type="text" value="{{ old('ministry', $member->ministry) }}" placeholder="Contoh: Tim Youth" class="input @error('ministry') input-error @enderror">
                    @error('ministry') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="cell_group" class="label">Komsel</label>
                    <input id="cell_group" name="cell_group" type="text" value="{{ old('cell_group', $member->cell_group) }}" placeholder="Contoh: KKS011 - Diana Dewi" class="input @error('cell_group') input-error @enderror">
                    @error('cell_group') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="notes" class="label">Catatan</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Catatan pelayanan atau informasi tambahan…" class="input resize-y @error('notes') input-error @enderror">{{ old('notes', $member->notes) }}</textarea>
                    @error('notes') <p class="error-text">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="card">
            <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800 sm:px-6">
                <h2 class="section-title">Foto jemaat</h2>
                <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Opsional. Format JPG, PNG, atau WebP, maksimal 2 MB.</p>
            </div>
            <div class="card-pad flex flex-col gap-4 sm:flex-row sm:items-center">
                <x-avatar :name="$member->full_name ?: 'Jemaat'" :src="$member->photo_url" size="h-16 w-16" text="text-lg" />
                <div class="flex-1">
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="block w-full text-xs text-slate-500 file:mr-3 file:rounded-xl file:border-0 file:bg-brand-50 file:px-4 file:py-2.5 file:text-xs file:font-bold file:text-brand-700 hover:file:bg-brand-100 dark:text-slate-400 dark:file:bg-slate-800 dark:file:text-slate-200">
                    @error('photo') <p class="error-text">{{ $message }}</p> @enderror
                    @if ($member->photo_url)
                        <p class="hint">Foto saat ini akan diganti jika Anda memilih file baru.</p>
                    @elseif ($member->photo_path)
                        <p class="hint">Referensi foto lama dari Excel tersimpan, tetapi file aslinya tidak ditemukan di server.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <a href="{{ $backUrl }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">
                <x-icon name="check" class="h-4 w-4" />
                {{ $member->exists ? 'Simpan Perubahan' : 'Simpan Jemaat' }}
            </button>
        </div>
    </form>
@endsection

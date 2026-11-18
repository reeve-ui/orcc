<?php

namespace App\Http\Requests;

use App\Enums\Gender;
use App\Models\Member;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('full_name')) {
            $this->merge(['full_name' => trim((string) $this->input('full_name'))]);
        }

        if ($this->filled('alias')) {
            $this->merge(['alias' => trim((string) $this->input('alias'))]);
        }

        if ($this->filled('nik')) {
            $this->merge(['nik' => preg_replace('/\s+/', '', (string) $this->input('nik'))]);
        }

        if ($this->filled('email')) {
            $this->merge(['email' => Str::lower(trim((string) $this->input('email')))]);
        }

        if ($this->filled('baptism_place')) {
            $this->merge(['baptism_place' => trim((string) $this->input('baptism_place'))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'family_id' => ['required', 'integer', 'exists:families,id'],
            'family_status' => ['required', Rule::in(Member::FAMILY_STATUSES)],
            'child_order' => [
                Rule::requiredIf(fn (): bool => $this->input('family_status') === Member::FAMILY_STATUS_ANGGOTA),
                'nullable',
                'integer',
                'min:1',
                'max:9',
            ],
            'gender' => ['required', Rule::in(array_keys(Gender::options()))],
            'full_name' => ['required', 'string', 'max:150'],
            'alias' => ['nullable', 'string', 'max:150'],
            'nik' => ['nullable', 'digits:16'],
            'phone' => ['nullable', 'string', 'max:25', 'regex:/^[0-9+\-().\s]{6,25}$/'],
            'whatsapp' => ['nullable', 'string', 'max:25', 'regex:/^[0-9+\-().\s]{6,25}$/'],
            'email' => ['nullable', 'email', 'max:150'],
            'birth_date' => [
                'required',
                'date',
                'before_or_equal:today',
                'after_or_equal:'.now()->subYears(99)->toDateString(),
            ],
            'baptism_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
                'after_or_equal:1900-01-01',
            ],
            'baptism_place' => ['nullable', 'string', 'max:150'],
            'marital_status' => ['required', Rule::in(Member::MARITAL_STATUSES)],
            'spouse_name' => ['nullable', 'string', 'max:150'],
            'ministry' => ['nullable', 'string', 'max:150'],
            'cell_group' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'family_id.required' => 'Keluarga (KKJ) wajib dipilih.',
            'family_id.exists' => 'Keluarga yang dipilih tidak ditemukan.',
            'family_status.required' => 'Status dalam keluarga wajib dipilih.',
            'child_order.required' => 'Anak ke- wajib diisi untuk status Anggota Keluarga.',
            'child_order.integer' => 'Anak ke- harus berupa angka.',
            'child_order.min' => 'Anak ke- minimal 1.',
            'child_order.max' => 'Anak ke- maksimal 9 karena nomor KTJ hanya memakai satu digit.',
            'gender.required' => 'Jenis kelamin wajib diisi.',
            'marital_status.required' => 'Status pernikahan wajib diisi.',
            'nik.digits' => 'NIK harus terdiri dari 16 angka.',
            'phone.regex' => 'Format nomor telepon tidak valid.',
            'whatsapp.regex' => 'Format nomor WhatsApp tidak valid.',
            'birth_date.required' => 'Tanggal lahir wajib diisi.',
            'birth_date.before_or_equal' => 'Tanggal lahir tidak boleh di masa depan.',
            'birth_date.after_or_equal' => 'Tanggal lahir minimal 99 tahun sebelum hari ini.',
            'baptism_date.before_or_equal' => 'Tanggal baptis tidak boleh di masa depan.',
            'baptism_date.after_or_equal' => 'Tanggal baptis tidak boleh sebelum tahun 1900.',
            'baptism_place.max' => 'Tempat baptis maksimal 150 karakter.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
        ];
    }
}

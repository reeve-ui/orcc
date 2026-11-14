<?php

namespace App\Http\Requests;

use App\Models\Family;
use App\Models\Region;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class FamilyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['head_name', 'province', 'city', 'district', 'village', 'postal_code', 'address'] as $field) {
            if ($this->filled($field)) {
                $this->merge([$field => trim((string) $this->input($field))]);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'head_name' => ['required', 'string', 'max:150'],
            'joined_year' => ['nullable', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
            'joy_number' => ['nullable', 'string', 'max:40'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'village' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'digits:5'],
            'address' => ['required', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $exists = Region::query()
                ->where('province', $this->input('province'))
                ->where('city', $this->input('city'))
                ->where('district', $this->input('district'))
                ->where('village', $this->input('village'))
                ->where('postal_code', $this->input('postal_code'))
                ->exists();

            if (! $exists) {
                $validator->errors()->add(
                    'village',
                    'Kombinasi provinsi, kabupaten/kota, kecamatan, kelurahan, dan kode pos tidak ditemukan. Pilih ulang alamat dari data wilayah.'
                );
            }
        });
    }

    protected function getRedirectUrl(): string
    {
        $family = $this->route('family');

        return $family instanceof Family
            ? route('families.edit', $family)
            : route('families.create');
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'province.required' => 'Provinsi wajib dipilih.',
            'city.required' => 'Kabupaten/kota wajib dipilih.',
            'district.required' => 'Kecamatan wajib dipilih.',
            'village.required' => 'Kelurahan/desa wajib dipilih.',
            'postal_code.required' => 'Kode pos wajib dipilih.',
            'postal_code.digits' => 'Kode pos harus terdiri dari 5 angka.',
            'address.required' => 'Alamat lengkap wajib diisi.',
            'photo.image' => 'Foto keluarga harus berupa gambar.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
        ];
    }
}

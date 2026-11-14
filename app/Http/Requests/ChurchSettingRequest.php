<?php

namespace App\Http\Requests;

use App\Models\ChurchSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

class ChurchSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('church_code')) {
            $this->merge(['church_code' => Str::upper(trim((string) $this->input('church_code')))]);
        }

        foreach (['name', 'tagline', 'address', 'phone', 'email', 'card_note', 'card_signature_name', 'card_signature_title'] as $field) {
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
            'church_code' => ['required', 'string', 'size:5', 'regex:/^[A-Z0-9]{5}$/'],
            'name' => ['required', 'string', 'max:150'],
            'tagline' => ['nullable', 'string', 'max:150'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'card_note' => ['nullable', 'string', 'max:500'],
            'card_signature_name' => ['nullable', 'string', 'max:100'],
            'card_signature_title' => ['nullable', 'string', 'max:100'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $settings = ChurchSetting::current();

            if ($settings->codeIsLocked() && $this->input('church_code') !== $settings->church_code) {
                $validator->errors()->add(
                    'church_code',
                    'Kode gereja sudah dipakai pada nomor KKJ dan tidak dapat diubah.'
                );
            }
        });
    }

    protected function getRedirectUrl(): string
    {
        return route('settings.edit');
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'church_code.required' => 'Kode gereja wajib diisi.',
            'church_code.size' => 'Kode gereja harus tepat 5 karakter.',
            'church_code.regex' => 'Kode gereja hanya boleh berisi huruf dan angka.',
            'name.required' => 'Nama gereja wajib diisi.',
            'address.required' => 'Alamat gereja wajib diisi.',
            'card_signature_name.max' => 'Nama penanda tangan maksimal 100 karakter.',
            'card_signature_title.max' => 'Jabatan penanda tangan maksimal 100 karakter.',
            'logo.image' => 'Logo harus berupa gambar.',
            'logo.mimes' => 'Logo harus berformat JPG, JPEG, atau PNG.',
            'logo.max' => 'Ukuran logo maksimal 2 MB.',
        ];
    }
}

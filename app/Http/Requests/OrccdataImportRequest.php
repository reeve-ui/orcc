<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class OrccdataImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'excel' => ['required', 'file', 'extensions:xlsx,xls', 'max:20480'],
            'dry_run' => ['nullable', 'boolean'],
            'fresh' => ['nullable', 'boolean'],
            'confirm_fresh' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->boolean('fresh') && ! $this->boolean('confirm_fresh')) {
                $validator->errors()->add(
                    'confirm_fresh',
                    'Centang konfirmasi penghapusan data sebelum menjalankan impor fresh.'
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
            'excel.required' => 'Pilih file Excel yang akan diimpor.',
            'excel.file' => 'Berkas impor tidak valid.',
            'excel.extensions' => 'File harus berformat .xlsx atau .xls.',
            'excel.max' => 'Ukuran file maksimal 20 MB.',
            'confirm_fresh.required' => 'Centang konfirmasi penghapusan data sebelum menjalankan impor fresh.',
        ];
    }
}

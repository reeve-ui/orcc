<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ResetDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $confirmation = $this->input('confirmation');

        if (is_string($confirmation)) {
            $this->merge([
                'confirmation' => Str::upper(trim($confirmation)),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'confirmation' => ['required', 'string', Rule::in(['RESET DATA'])],
            'confirm_reset' => ['accepted'],
        ];
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
            'confirmation.required' => 'Ketik RESET DATA untuk mengonfirmasi penghapusan.',
            'confirmation.in' => 'Teks konfirmasi tidak cocok. Ketik RESET DATA.',
            'confirm_reset.accepted' => 'Centang pernyataan bahwa Anda memahami tindakan ini tidak dapat dibatalkan.',
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChurchSetting extends Model
{
    protected $fillable = [
        'church_code',
        'name',
        'tagline',
        'address',
        'phone',
        'email',
        'logo_path',
        'card_note',
        'card_signature_name',
        'card_signature_title',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'church_code' => config('church.code'),
                'name' => config('church.name', 'Sekretariat Gereja'),
                'tagline' => config('church.tagline'),
                'address' => config('church.address'),
                'phone' => config('church.phone'),
                'email' => config('church.email'),
                'card_note' => config('church.card.note'),
                'card_signature_name' => config('church.card.signature_name'),
                'card_signature_title' => config('church.card.signature_title'),
            ]
        );
    }

    /**
     * Kode gereja selalu disimpan dan dibaca dalam huruf besar.
     */
    protected function churchCode(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value === null ? null : Str::upper(trim((string) $value)),
            set: fn ($value) => $value === null ? null : Str::upper(trim((string) $value)),
        );
    }

    public function isConfigured(): bool
    {
        return is_string($this->church_code)
            && preg_match('/^[A-Z0-9]{5}$/', $this->church_code) === 1;
    }

    /**
     * Kode gereja tidak boleh diubah setelah dipakai pada nomor KKJ.
     */
    public function codeIsLocked(): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        return Family::query()
            ->where('kkj_id', 'like', $this->church_code.'%')
            ->exists();
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (blank($this->logo_path)) {
            return null;
        }

        $disk = Storage::disk('public');

        return $disk->exists($this->logo_path) ? $disk->url($this->logo_path) : null;
    }

    public function logoDataUri(): ?string
    {
        if (blank($this->logo_path)) {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($this->logo_path)) {
            return null;
        }

        $mime = $disk->mimeType($this->logo_path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode($disk->get($this->logo_path));
    }

    public function applyToConfig(): void
    {
        config([
            'church.code' => $this->church_code,
            'church.name' => $this->name ?: config('church.name'),
            'church.tagline' => $this->tagline ?: config('church.tagline'),
            'church.address' => $this->address ?: config('church.address'),
            'church.phone' => $this->phone ?: config('church.phone'),
            'church.email' => $this->email ?: config('church.email'),
            'church.logo_path' => $this->logo_path,
            'church.logo_url' => $this->logo_url,
            'church.card.note' => $this->card_note ?: config('church.card.note'),
            'church.card.signature_name' => $this->card_signature_name ?: config('church.card.signature_name'),
            'church.card.signature_title' => $this->card_signature_title ?: config('church.card.signature_title'),
        ]);
    }
}

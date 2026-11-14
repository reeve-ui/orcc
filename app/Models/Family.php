<?php

namespace App\Models;

use App\Services\KkjNumberGenerator;
use Database\Factories\FamilyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Family extends Model
{
    /** @use HasFactory<FamilyFactory> */
    use HasFactory;

    protected $fillable = [
        'kkj_id',
        'head_name',
        'joined_year',
        'joy_number',
        'province',
        'city',
        'district',
        'village',
        'postal_code',
        'address',
        'photo_path',
        'qr_payload',
        'legacy_raw',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'joined_year' => 'integer',
            'legacy_raw' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Family $family): void {
            if (blank($family->kkj_id)) {
                $family->kkj_id = app(KkjNumberGenerator::class)->next();
            }

            if (blank($family->verification_token)) {
                $family->verification_token = static::generateVerificationToken();
            }
        });
    }

    public static function generateVerificationToken(): string
    {
        do {
            $token = Str::lower(Str::random(32));
        } while (static::query()->where('verification_token', $token)->exists());

        return $token;
    }

    /**
     * Pastikan keluarga lama tetap memiliki token sebelum kartu dicetak.
     */
    public function ensureVerificationToken(): string
    {
        if (blank($this->verification_token)) {
            $this->forceFill([
                'verification_token' => static::generateVerificationToken(),
            ])->save();
        }

        return $this->verification_token;
    }

    public function getVerificationUrlAttribute(): ?string
    {
        if (blank($this->verification_token)) {
            return null;
        }

        return route('families.verify', ['token' => $this->verification_token]);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%'.$term.'%';

        return $query->where(function (Builder $query) use ($like): void {
            $query->where('kkj_id', 'like', $like)
                ->orWhere('head_name', 'like', $like)
                ->orWhere('address', 'like', $like)
                ->orWhere('village', 'like', $like)
                ->orWhere('district', 'like', $like);
        });
    }

    public function getInitialsAttribute(): string
    {
        $words = preg_split('/\s+/', trim((string) $this->head_name)) ?: [];
        $initials = collect($words)
            ->filter()
            ->take(2)
            ->map(fn (string $word) => Str::upper(Str::substr($word, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : 'KKJ';
    }

    public function hasCustomPhoto(): bool
    {
        if (blank($this->photo_path)) {
            return false;
        }

        if (Str::startsWith($this->photo_path, ['http://', 'https://'])) {
            return true;
        }

        return Storage::disk('public')->exists($this->photo_path);
    }

    public function getHasCustomPhotoAttribute(): bool
    {
        return $this->hasCustomPhoto();
    }

    public function getPhotoUrlAttribute(): string
    {
        if ($this->hasCustomPhoto()) {
            if (Str::startsWith($this->photo_path, ['http://', 'https://'])) {
                return $this->photo_path;
            }

            return Storage::disk('public')->url($this->photo_path);
        }

        return asset('images/family-default.png');
    }

    public function getLocationLineAttribute(): string
    {
        $parts = array_filter([
            $this->village,
            $this->district,
            $this->city,
            $this->province,
        ]);

        return $parts !== [] ? implode(', ', $parts) : 'Alamat belum dilengkapi';
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->village,
            $this->district,
            $this->city,
            $this->province,
            $this->postal_code,
        ]);

        return $parts !== [] ? implode(', ', $parts) : 'Alamat belum dilengkapi';
    }
}

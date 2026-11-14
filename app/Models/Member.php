<?php

namespace App\Models;

use App\Enums\Gender;
use App\Support\AgeCategory;
use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory;

    public const FAMILY_STATUS_ANGGOTA = 'Anggota Keluarga';

    public const FAMILY_STATUSES = [
        'Kepala Keluarga',
        'Pasangan',
        'Anak',
        'Saudara',
        self::FAMILY_STATUS_ANGGOTA,
    ];

    public const MARITAL_STATUS_MARRIED = 'Telah Menikah';

    public const MARITAL_STATUSES = [
        'Belum Menikah',
        self::MARITAL_STATUS_MARRIED,
        'Pernah Menikah',
    ];

    public const STATUS_RANK = [
        'Kepala Keluarga' => 1,
        'Pasangan' => 2,
        'Anak' => 3,
        'Saudara' => 4,
        'Anggota Keluarga' => 5,
    ];

    protected $fillable = [
        'family_id',
        'ktj_id',
        'family_status',
        'child_order',
        'gender',
        'full_name',
        'alias',
        'nik',
        'phone',
        'whatsapp',
        'email',
        'birth_date',
        'marital_status',
        'spouse_name',
        'photo_path',
        'qr_payload',
        'ministry',
        'cell_group',
        'notes',
        'data_flags',
        'legacy_raw',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'family_status' => 'string',
            'child_order' => 'integer',
            'gender' => Gender::class,
            'birth_date' => 'date',
            'data_flags' => 'array',
            'legacy_raw' => 'array',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%'.$term.'%';

        return $query->where(function (Builder $query) use ($like): void {
            $query->where('full_name', 'like', $like)
                ->orWhere('alias', 'like', $like)
                ->orWhere('ktj_id', 'like', $like)
                ->orWhere('nik', 'like', $like)
                ->orWhere('phone', 'like', $like)
                ->orWhereHas('family', function (Builder $family) use ($like): void {
                    $family->where('kkj_id', 'like', $like)
                        ->orWhere('head_name', 'like', $like);
                });
        });
    }

    public function scopeAgeCategory(Builder $query, string $category): Builder
    {
        $bounds = AgeCategory::bounds($category);

        if ($bounds === null) {
            return $query->whereNull('birth_date');
        }

        [$min, $max] = $bounds;
        $today = now()->startOfDay();

        $query->whereNotNull('birth_date')
            ->whereDate('birth_date', '<=', $today->copy()->subYears($min));

        if ($max !== null) {
            $query->whereDate('birth_date', '>', $today->copy()->subYears($max + 1));
        }

        return $query;
    }

    public function getAgeAttribute(): ?int
    {
        if (! $this->birth_date) {
            return null;
        }

        return max(0, (int) $this->birth_date->diffInYears(now()));
    }

    public function getAgeCategoryAttribute(): string
    {
        return AgeCategory::fromBirthDate($this->birth_date);
    }

    public function getStatusRankAttribute(): int
    {
        return self::STATUS_RANK[$this->family_status] ?? 99;
    }

    public function getInitialsAttribute(): string
    {
        $words = preg_split('/\s+/', trim((string) $this->full_name)) ?: [];
        $initials = collect($words)
            ->filter()
            ->take(2)
            ->map(fn (string $word) => Str::upper(Str::substr($word, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : '?';
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo_path) {
            return null;
        }

        if (Str::startsWith($this->photo_path, ['http://', 'https://'])) {
            return $this->photo_path;
        }

        $disk = Storage::disk('public');

        return $disk->exists($this->photo_path) ? $disk->url($this->photo_path) : null;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->alias ? "{$this->full_name} ({$this->alias})" : $this->full_name;
    }
}

<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'member_id',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => Role::class,
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn (string $value): string => Str::lower(trim($value)),
        );
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    public function isStaff(): bool
    {
        return $this->role->isStaff();
    }

    public function getInitialsAttribute(): string
    {
        $words = preg_split('/\s+/', trim($this->name)) ?: [];
        $initials = collect($words)
            ->filter()
            ->take(2)
            ->map(fn (string $word) => Str::upper(Str::substr($word, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : '?';
    }
}

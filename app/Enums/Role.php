<?php

namespace App\Enums;

enum Role: int
{
    case Admin = 1;
    case Leader = 2;
    case Pengerja = 3;
    case Jemaat = 4;

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Leader => 'Leader',
            self::Pengerja => 'Pengerja',
            self::Jemaat => 'Jemaat',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Admin => 'Akses penuh, termasuk pengelolaan akun pengguna.',
            self::Leader => 'Pendeta, ketua majelis, ketua yayasan, atau kepala sekretariat. Melihat & mengelola seluruh data, termasuk menghapus data.',
            self::Pengerja => 'Melihat & mengelola data, tanpa hak menghapus.',
            self::Jemaat => 'Hanya dapat melihat data keluarganya sendiri.',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Admin => 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/30',
            self::Leader => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/30',
            self::Pengerja => 'bg-sky-50 text-sky-700 ring-sky-600/20 dark:bg-sky-500/10 dark:text-sky-300 dark:ring-sky-500/30',
            self::Jemaat => 'bg-slate-100 text-slate-600 ring-slate-500/20 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-600/40',
        };
    }

    public function isStaff(): bool
    {
        return $this->value <= self::Pengerja->value;
    }

    public function canManageData(): bool
    {
        return $this->isStaff();
    }

    public function canDeleteData(): bool
    {
        return $this->value <= self::Leader->value;
    }

    public function canManageUsers(): bool
    {
        return $this === self::Admin;
    }

    /**
     * @return array<int, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $role) => [$role->value => $role->label()])
            ->all();
    }
}

<?php

namespace App\Support;

use Carbon\CarbonInterface;

final class AgeCategory
{
    public const UNKNOWN = 'Belum diketahui';

    /**
     * @return array<string, array{0: int, 1: int|null}>
     */
    public static function groups(): array
    {
        return config('church.age_groups', [
            'Anak' => [0, 12],
            'Remaja' => [13, 17],
            'Pemuda' => [18, 30],
            'Dewasa' => [31, 59],
            'Lansia' => [60, null],
        ]);
    }

    /**
     * @return array<int, string>
     */
    public static function labels(): array
    {
        return array_keys(self::groups());
    }

    /**
     * @return array{0: int, 1: int|null}|null
     */
    public static function bounds(string $label): ?array
    {
        return self::groups()[$label] ?? null;
    }

    public static function fromBirthDate(?CarbonInterface $birthDate, ?CarbonInterface $on = null): string
    {
        if ($birthDate === null) {
            return self::UNKNOWN;
        }

        $on ??= now();

        if ($birthDate->isAfter($on)) {
            return self::UNKNOWN;
        }

        $age = (int) $birthDate->diffInYears($on);

        foreach (self::groups() as $label => [$min, $max]) {
            if ($age >= $min && ($max === null || $age <= $max)) {
                return $label;
            }
        }

        return self::UNKNOWN;
    }

    public static function rangeLabel(string $label): string
    {
        $bounds = self::bounds($label);

        if ($bounds === null) {
            return 'Tanggal lahir belum diisi';
        }

        [$min, $max] = $bounds;

        return $max === null ? "{$min}+ tahun" : "{$min}–{$max} tahun";
    }

    public static function badgeClasses(string $label): string
    {
        return match ($label) {
            'Anak' => 'bg-sky-50 text-sky-700 ring-sky-600/20 dark:bg-sky-500/10 dark:text-sky-300 dark:ring-sky-500/30',
            'Remaja' => 'bg-violet-50 text-violet-700 ring-violet-600/20 dark:bg-violet-500/10 dark:text-violet-300 dark:ring-violet-500/30',
            'Pemuda' => 'bg-brand-50 text-brand-700 ring-brand-600/20 dark:bg-brand-500/10 dark:text-brand-300 dark:ring-brand-500/30',
            'Dewasa' => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/30',
            'Lansia' => 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/30',
            default => 'bg-slate-100 text-slate-600 ring-slate-500/20 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-600/40',
        };
    }

    public static function barColor(string $label): string
    {
        return match ($label) {
            'Anak' => '#0ea5e9',
            'Remaja' => '#8b5cf6',
            'Pemuda' => '#0d9488',
            'Dewasa' => '#f59e0b',
            'Lansia' => '#e11d48',
            default => '#94a3b8',
        };
    }
}

<?php

namespace App\Enums;

enum Gender: string
{
    case Pria = 'Pria';
    case Wanita = 'Wanita';

    public function label(): string
    {
        return $this->value;
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Pria => 'L',
            self::Wanita => 'P',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pria => 'bg-sky-50 text-sky-700 ring-sky-600/20 dark:bg-sky-500/10 dark:text-sky-300 dark:ring-sky-500/30',
            self::Wanita => 'bg-pink-50 text-pink-700 ring-pink-600/20 dark:bg-pink-500/10 dark:text-pink-300 dark:ring-pink-500/30',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $gender) => [$gender->value => $gender->label()])
            ->all();
    }
}

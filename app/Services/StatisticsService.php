<?php

namespace App\Services;

use App\Enums\Gender;
use App\Models\Family;
use App\Models\Member;
use App\Support\AgeCategory;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StatisticsService
{
    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $totalMembers = Member::query()->count();
        $male = Member::query()->where('gender', Gender::Pria->value)->count();
        $female = Member::query()->where('gender', Gender::Wanita->value)->count();
        $unknownGender = max(0, $totalMembers - $male - $female);

        $genderTotal = max(1, $male + $female + $unknownGender);

        $age = [];
        foreach (AgeCategory::labels() as $label) {
            $age[$label] = Member::query()->ageCategory($label)->count();
        }

        $marital = Member::query()
            ->selectRaw('marital_status, COUNT(*) as total')
            ->whereNotNull('marital_status')
            ->where('marital_status', '<>', '')
            ->groupBy('marital_status')
            ->orderByDesc('total')
            ->pluck('total', 'marital_status')
            ->all();

        $districts = Family::query()
            ->selectRaw('district, COUNT(*) as total')
            ->whereNotNull('district')
            ->where('district', '<>', '')
            ->groupBy('district')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'families' => Family::query()->count(),
            'members' => $totalMembers,
            'male' => $male,
            'female' => $female,
            'unknown_gender' => $unknownGender,
            'gender_share' => [
                'male' => round($male / $genderTotal * 100, 1),
                'female' => round($female / $genderTotal * 100, 1),
                'unknown' => round($unknownGender / $genderTotal * 100, 1),
            ],
            'age' => $age,
            'unknown_age' => Member::query()->whereNull('birth_date')->count(),
            'incomplete' => Member::query()
                ->where(function ($query): void {
                    $query->whereNull('birth_date')->orWhereNull('gender');
                })
                ->count(),
            'marital' => $marital,
            'districts' => $districts,
            'joined_this_year' => Family::query()->where('joined_year', now()->year)->count(),
            'head_of_family' => Member::query()->where('family_status', 'Kepala Keluarga')->count(),
        ];
    }

    /**
     * @return Collection<int, array{member: Member, next: Carbon, turning: int}>
     */
    public function upcomingBirthdays(int $days = 30, int $limit = 6): Collection
    {
        $today = now()->startOfDay();
        $until = $today->copy()->addDays($days);

        return Member::query()
            ->with('family')
            ->whereNotNull('birth_date')
            ->get()
            ->map(function (Member $member) use ($today): array {
                $next = $member->birth_date->copy()->year($today->year);

                if ($next->lt($today)) {
                    $next->addYear();
                }

                return [
                    'member' => $member,
                    'next' => $next,
                    'turning' => (int) $member->birth_date->diffInYears($next),
                ];
            })
            ->filter(fn (array $item) => $item['next']->betweenIncluded($today, $until))
            ->sortBy(fn (array $item) => $item['next']->timestamp)
            ->take($limit)
            ->values();
    }
}

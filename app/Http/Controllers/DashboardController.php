<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Services\StatisticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, StatisticsService $statistics): View
    {
        $user = $request->user();

        if (! $user->isStaff()) {
            $member = $user->member?->load('family.members');

            return view('dashboard-member', [
                'member' => $member,
            ]);
        }

        $stats = $statistics->summary();

        $recentFamilies = Family::query()
            ->withCount('members')
            ->latest()
            ->limit(6)
            ->get();

        return view('dashboard', [
            'stats' => $stats,
            'recentFamilies' => $recentFamilies,
            'birthdays' => $statistics->upcomingBirthdays(),
        ]);
    }
}

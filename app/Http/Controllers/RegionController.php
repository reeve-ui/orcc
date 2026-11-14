<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function provinces(): JsonResponse
    {
        return response()->json([
            'items' => Region::query()
                ->distinct()
                ->orderBy('province')
                ->pluck('province')
                ->values(),
        ]);
    }

    public function cities(Request $request): JsonResponse
    {
        $data = $request->validate([
            'province' => ['required', 'string', 'max:100'],
        ]);

        return response()->json([
            'items' => Region::query()
                ->where('province', $data['province'])
                ->distinct()
                ->orderBy('city')
                ->pluck('city')
                ->values(),
        ]);
    }

    public function districts(Request $request): JsonResponse
    {
        $data = $request->validate([
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
        ]);

        return response()->json([
            'items' => Region::query()
                ->where('province', $data['province'])
                ->where('city', $data['city'])
                ->distinct()
                ->orderBy('district')
                ->pluck('district')
                ->values(),
        ]);
    }

    public function villages(Request $request): JsonResponse
    {
        $data = $request->validate([
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
        ]);

        return response()->json([
            'items' => Region::query()
                ->where('province', $data['province'])
                ->where('city', $data['city'])
                ->where('district', $data['district'])
                ->distinct()
                ->orderBy('village')
                ->pluck('village')
                ->values(),
        ]);
    }

    public function postalCodes(Request $request): JsonResponse
    {
        $data = $request->validate([
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'village' => ['required', 'string', 'max:100'],
        ]);

        return response()->json([
            'items' => Region::query()
                ->where('province', $data['province'])
                ->where('city', $data['city'])
                ->where('district', $data['district'])
                ->where('village', $data['village'])
                ->distinct()
                ->orderBy('postal_code')
                ->pluck('postal_code')
                ->values(),
        ]);
    }
}

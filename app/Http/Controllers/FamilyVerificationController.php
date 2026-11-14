<?php

namespace App\Http\Controllers;

use App\Models\Family;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class FamilyVerificationController extends Controller
{
    /**
     * Halaman publik yang dibuka lewat QR pada kartu KKJ.
     * Hanya menampilkan ringkasan yang aman; data pribadi tidak ditampilkan.
     */
    public function show(string $token): View
    {
        $family = Family::query()
            ->where('verification_token', Str::lower($token))
            ->withCount('members')
            ->firstOrFail();

        return view('verify.family', [
            'family' => $family,
            'church' => config('church'),
        ]);
    }
}

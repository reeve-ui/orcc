<?php

namespace App\Http\Controllers;

use App\Services\UpdateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AppUpdateController extends Controller
{
    public function check(Request $request, UpdateService $updates): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $check = $updates->check(true);

        if (! empty($check['error'])) {
            return redirect()->route('settings.edit')->with('error', (string) $check['error']);
        }

        return redirect()->route('settings.edit')->with('status', $check['update_available']
            ? 'Pembaruan tersedia dari GitHub.'
            : 'Aplikasi sudah menggunakan versi terbaru dari GitHub.');
    }

    public function update(Request $request, UpdateService $updates): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $result = $updates->start((int) $request->user()->id);

        return redirect()->route('settings.edit')
            ->with($result['started'] ? 'status' : 'error', $result['message']);
    }

    public function status(Request $request, UpdateService $updates): JsonResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        return response()->json([
            'status' => $updates->status(),
            'check' => $updates->lastCheck(),
        ]);
    }
}

<?php

namespace App\Providers;

use App\Models\ChurchSetting;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useTailwind();

        Carbon::setLocale(config('app.locale', 'id'));

        $this->applyChurchSettings();
    }

    /**
     * Identitas gereja disimpan di database agar dapat diubah dari aplikasi.
     * Nilai config tetap menjadi cadangan bila tabel belum tersedia (mis. saat migrate).
     */
    private function applyChurchSettings(): void
    {
        try {
            if (! Schema::hasTable('church_settings')) {
                return;
            }

            ChurchSetting::current()->applyToConfig();
        } catch (Throwable) {
            // Database belum siap (migrasi/instalasi awal); gunakan nilai config.
        }
    }
}

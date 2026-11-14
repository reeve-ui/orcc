<?php

namespace Database\Seeders;

use App\Models\Region;
use App\Services\RegionImporter;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        if (Region::query()->exists()) {
            $this->command?->info('Data wilayah sudah tersedia, impor dilewati.');

            return;
        }

        $result = app(RegionImporter::class)->import(database_path('data/kodepos.csv'));

        $this->command?->info("Data wilayah: {$result['total']} baris tersedia.");
    }
}

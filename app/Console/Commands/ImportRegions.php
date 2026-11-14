<?php

namespace App\Console\Commands;

use App\Services\RegionImporter;
use Illuminate\Console\Command;
use Throwable;

class ImportRegions extends Command
{
    protected $signature = 'regions:import
                            {path? : Lokasi CSV wilayah (default: database/data/kodepos.csv)}
                            {--fresh : Hapus seluruh data wilayah sebelum impor}';

    protected $description = 'Impor data provinsi, kabupaten/kota, kecamatan, kelurahan, dan kode pos dari CSV.';

    public function handle(RegionImporter $importer): int
    {
        $path = $this->argument('path') ?: database_path('data/kodepos.csv');

        try {
            $result = $importer->import($path, (bool) $this->option('fresh'));
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Keterangan', 'Jumlah'],
            [
                ['Baris dibaca', $result['read']],
                ['Baris diimpor', $result['imported']],
                ['Duplikat diabaikan', $result['ignored']],
                ['Baris dilewati', $result['skipped']],
                ['Total wilayah di database', $result['total']],
            ]
        );

        $this->info('Impor wilayah selesai.');

        return self::SUCCESS;
    }
}

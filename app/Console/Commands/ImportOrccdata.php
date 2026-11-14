<?php

namespace App\Console\Commands;

use App\Services\OrccdataImporter;
use Illuminate\Console\Command;
use Throwable;

class ImportOrccdata extends Command
{
    protected $signature = 'orccdata:import
                            {path? : Lokasi file Excel (default: storage/app/import/orccdata.xlsx)}
                            {--fresh : Hapus seluruh data KKJ dan jemaat sebelum impor}
                            {--dry-run : Proses impor lalu batalkan transaksi (tidak menyimpan)}';

    protected $description = 'Impor data KKJ dan jemaat dari workbook ORCCDATA (sheet KKJ dan JEMAAT).';

    public function handle(OrccdataImporter $importer): int
    {
        $path = $this->argument('path') ?: storage_path('app/import/orccdata.xlsx');

        if (! is_file($path)) {
            $this->error("File tidak ditemukan: {$path}");

            return self::FAILURE;
        }

        if ($this->option('fresh') && ! $this->option('dry-run')) {
            if (! $this->confirm('Semua data KKJ dan jemaat akan dihapus sebelum impor. Lanjutkan?', false)) {
                $this->warn('Impor dibatalkan.');

                return self::FAILURE;
            }
        }

        $this->info("Membaca {$path}");

        try {
            $result = $importer->import(
                $path,
                (bool) $this->option('fresh'),
                (bool) $this->option('dry-run')
            );
        } catch (Throwable $exception) {
            $this->error('Impor gagal, seluruh perubahan dibatalkan.');
            $this->error($exception->getMessage());
            report($exception);

            return self::FAILURE;
        }

        $this->line('  KKJ    : '.$result['kkj_rows'].' baris');
        $this->line('  JEMAAT : '.$result['member_rows'].' baris');

        if ($result['dry_run']) {
            $this->warn('Mode dry-run: semua perubahan dibatalkan.');
        }

        $this->newLine();
        $this->table(
            ['Keterangan', 'Jumlah'],
            [
                ['KKJ baru', $result['families_created']],
                ['KKJ diperbarui', $result['families_updated']],
                ['Jemaat baru', $result['members_created']],
                ['Jemaat diperbarui', $result['members_updated']],
                ['Baris dilewati', $result['skipped']],
            ]
        );

        if ($result['flags'] !== []) {
            $this->warn('Catatan kualitas data (perlu dilengkapi manual):');
            foreach ($result['flags'] as $flag => $count) {
                $this->line("  - {$flag}: {$count}");
            }
        }

        $this->info('Selesai. Data non-kosong yang sudah ada tidak ditimpa; gunakan --fresh untuk impor ulang dari nol.');

        return self::SUCCESS;
    }
}

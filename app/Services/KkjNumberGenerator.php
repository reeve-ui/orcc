<?php

namespace App\Services;

use App\Models\Family;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class KkjNumberGenerator
{
    /**
     * Format: {kode 5}{00}{tahun 2}{urutan 4}
     * Contoh: SEKRT00260001
     */
    public function next(): string
    {
        $code = strtoupper(trim((string) config('church.code')));

        if (! preg_match('/^[A-Z0-9]{5}$/', $code)) {
            throw new RuntimeException('Kode gereja belum diatur. Isi kode 5 karakter di Pengaturan Gereja.');
        }

        $year = (int) now()->format('Y');
        $prefix = $code.'00'.now()->format('y');

        do {
            $sequence = $this->nextSequence($year);

            if ($sequence > 9999) {
                throw new RuntimeException("Nomor KKJ tahun {$year} sudah mencapai 9999.");
            }

            $candidate = $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
        } while (Family::query()->where('kkj_id', $candidate)->exists());

        return $candidate;
    }

    public function preview(): string
    {
        $code = strtoupper(trim((string) config('church.code')));

        return ($code !== '' ? $code : 'SEKRT').'00'.now()->format('y').'····';
    }

    private function nextSequence(int $year): int
    {
        return DB::transaction(function () use ($year): int {
            DB::table('kkj_sequences')->insertOrIgnore([
                'sequence_year' => $year,
                'last_number' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('kkj_sequences')
                ->where('sequence_year', $year)
                ->increment('last_number', 1, ['updated_at' => now()]);

            return (int) DB::table('kkj_sequences')
                ->where('sequence_year', $year)
                ->value('last_number');
        });
    }
}

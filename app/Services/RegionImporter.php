<?php

namespace App\Services;

use App\Models\Region;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RegionImporter
{
    /**
     * @return array{read: int, imported: int, skipped: int, ignored: int, total: int}
     */
    public function import(string $path, bool $fresh = false): array
    {
        if (! is_file($path)) {
            throw new RuntimeException("File wilayah tidak ditemukan: {$path}");
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException("File wilayah tidak dapat dibaca: {$path}");
        }

        try {
            // Lewati header: province,city,district,subdistrict,postal_code
            fgetcsv($handle);

            $before = $fresh ? 0 : Region::query()->count();
            $read = 0;
            $skipped = 0;

            DB::transaction(function () use ($handle, $fresh, &$read, &$skipped): void {
                if ($fresh) {
                    Region::query()->delete();
                }

                $batch = [];

                while (($row = fgetcsv($handle)) !== false) {
                    $read++;

                    if ($row === [null] || count($row) < 5) {
                        $skipped++;

                        continue;
                    }

                    $values = array_map(
                        fn ($value) => trim(preg_replace('/\s+/', ' ', (string) $value) ?? ''),
                        array_slice($row, 0, 5)
                    );

                    [$province, $city, $district, $village, $postalCode] = $values;

                    $maxLength = max(array_map('mb_strlen', $values));

                    if (
                        $province === '' || $city === '' || $district === '' || $village === ''
                        || ! preg_match('/^\d{5}$/', $postalCode)
                        || $maxLength > 100
                    ) {
                        $skipped++;

                        continue;
                    }

                    $batch[] = [
                        'province' => $province,
                        'city' => $city,
                        'district' => $district,
                        'village' => $village,
                        'postal_code' => $postalCode,
                    ];

                    if (count($batch) >= 1000) {
                        Region::query()->insertOrIgnore($batch);
                        $batch = [];
                    }
                }

                if ($batch !== []) {
                    Region::query()->insertOrIgnore($batch);
                }
            });

            $after = Region::query()->count();
            $imported = max(0, $after - $before);

            return [
                'read' => $read,
                'imported' => $imported,
                'skipped' => $skipped,
                'ignored' => max(0, $read - $skipped - $imported),
                'total' => $after,
            ];
        } finally {
            fclose($handle);
        }
    }
}

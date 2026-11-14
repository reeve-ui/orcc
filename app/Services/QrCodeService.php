<?php

namespace App\Services;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use Throwable;

class QrCodeService
{
    /**
     * Menghasilkan matriks QR berupa array baris berisi boolean.
     * Matriks dirender sebagai tabel HTML agar aman di dompdf
     * (tanpa butuh ekstensi GD/Imagick).
     *
     * @return array<int, array<int, bool>>
     */
    public function matrix(string $payload, int $quietZone = 2): array
    {
        try {
            $qrCode = Encoder::encode($payload, ErrorCorrectionLevel::M());
        } catch (Throwable) {
            return [];
        }

        $source = $qrCode->getMatrix();
        $size = $source->getWidth();
        $dimension = $size + ($quietZone * 2);

        $grid = array_fill(0, $dimension, array_fill(0, $dimension, false));

        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $grid[$y + $quietZone][$x + $quietZone] = $source->get($x, $y) === 1;
            }
        }

        return $grid;
    }
}

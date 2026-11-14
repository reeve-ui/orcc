<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class LogoService
{
    private const MAX_DIMENSION = 512;

    private const MAX_SOURCE_PIXELS = 25_000_000;

    /**
     * Simpan logo sebagai PNG dan perkecil otomatis tanpa merusak rasio.
     */
    public function store(UploadedFile $file): string
    {
        $this->ensureGdIsAvailable();

        $source = $this->loadSource($file);
        $resized = $this->resize($source);

        ob_start();
        $encoded = imagepng($resized, null, 9);
        $contents = ob_get_clean();

        imagedestroy($resized);

        if (! $encoded || ! is_string($contents) || $contents === '') {
            throw new RuntimeException('Logo gagal diproses menjadi PNG.');
        }

        $path = 'church/logo-'.Str::random(40).'.png';

        if (! Storage::disk('public')->put($path, $contents)) {
            throw new RuntimeException('Logo gagal disimpan.');
        }

        return $path;
    }

    private function ensureGdIsAvailable(): void
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagecreatetruecolor')) {
            throw ValidationException::withMessages([
                'logo' => 'Server belum mendukung pemrosesan gambar. Aktifkan ekstensi GD.',
            ]);
        }
    }

    private function loadSource(UploadedFile $file): \GdImage
    {
        $path = $file->getRealPath();
        $size = $path !== false ? @getimagesize($path) : false;

        if ($size === false) {
            throw ValidationException::withMessages([
                'logo' => 'File logo tidak dapat dibaca sebagai gambar.',
            ]);
        }

        [$width, $height] = $size;

        if ($width < 1 || $height < 1) {
            throw ValidationException::withMessages([
                'logo' => 'Ukuran logo tidak valid.',
            ]);
        }

        if ($width * $height > self::MAX_SOURCE_PIXELS) {
            throw ValidationException::withMessages([
                'logo' => 'Resolusi logo terlalu besar. Gunakan gambar maksimal 25 megapiksel.',
            ]);
        }

        $contents = $path !== false ? file_get_contents($path) : false;
        $image = is_string($contents) ? @imagecreatefromstring($contents) : false;

        if ($image === false) {
            throw ValidationException::withMessages([
                'logo' => 'File logo tidak dapat diproses. Gunakan JPG atau PNG yang valid.',
            ]);
        }

        return $image;
    }

    private function resize(\GdImage $source): \GdImage
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $ratio = min(1, self::MAX_DIMENSION / max($width, $height));
        $targetWidth = max(1, (int) round($width * $ratio));
        $targetHeight = max(1, (int) round($height * $ratio));

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($canvas === false) {
            imagedestroy($source);

            throw new RuntimeException('Logo gagal diproses.');
        }

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $transparent);
        imagealphablending($canvas, true);

        $copied = imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $width,
            $height
        );

        imagedestroy($source);

        if (! $copied) {
            imagedestroy($canvas);

            throw new RuntimeException('Logo gagal diperkecil.');
        }

        return $canvas;
    }
}

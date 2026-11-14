<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ResetUserPassword extends Command
{
    protected $signature = 'orcc4church:reset-password
                            {email : Email pengguna yang akan direset}
                            {--activate : Aktifkan akun setelah kata sandi direset}';

    protected $description = 'Mengatur ulang kata sandi pengguna ORCC4CHURCH.';

    public function handle(): int
    {
        $email = Str::lower(trim((string) $this->argument('email')));

        $user = User::query()->where('email', $email)->first()
            ?? User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        if ($user === null) {
            $this->error("Pengguna dengan email {$email} tidak ditemukan.");

            return self::FAILURE;
        }

        $password = $this->secret('Kata sandi baru (minimal 8 karakter)');

        if (! is_string($password) || Str::length($password) < 8) {
            $this->error('Kata sandi minimal 8 karakter.');

            return self::FAILURE;
        }

        $confirmation = $this->secret('Ulangi kata sandi baru');

        if ($password !== $confirmation) {
            $this->error('Konfirmasi kata sandi tidak sama.');

            return self::FAILURE;
        }

        $user->forceFill([
            'password' => $password,
            'is_active' => $this->option('activate') ? true : $user->is_active,
        ])->save();

        $this->info("Kata sandi untuk {$user->email} berhasil diubah.");

        if (! $user->is_active) {
            $this->warn('Akun ini masih nonaktif. Jalankan ulang dengan opsi --activate atau aktifkan dari halaman pengguna.');
        }

        return self::SUCCESS;
    }
}

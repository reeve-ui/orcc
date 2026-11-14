<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) env('ADMIN_EMAIL', 'admin@sekretariatgereja.test');

        $admin = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => (string) env('ADMIN_NAME', 'Administrator'),
                'password' => (string) env('ADMIN_PASSWORD', 'password'),
                'role' => Role::Admin,
                'is_active' => true,
            ]
        );

        if (! $admin->wasRecentlyCreated) {
            $admin->forceFill([
                'role' => Role::Admin,
                'is_active' => true,
            ])->save();
        }

        $this->command?->info("Akun admin: {$email}");
    }
}

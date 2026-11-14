<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dapat_login_dengan_email_huruf_besar(): void
    {
        $admin = $this->user(Role::Admin, 'admin.login@example.test');

        $this->post(route('login.store'), [
            'email' => 'ADMIN.LOGIN@EXAMPLE.TEST',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->assertNotNull($admin->fresh()->last_login_at);
    }

    public function test_login_dapat_menemukan_email_lama_yang_masih_huruf_besar(): void
    {
        DB::table('users')->insert([
            'name' => 'Admin Legacy',
            'email' => 'ADMIN.LEGACY@EXAMPLE.TEST',
            'password' => Hash::make('password'),
            'role' => Role::Admin->value,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->post(route('login.store'), [
            'email' => 'admin.legacy@example.test',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_login_ditolak_jika_kata_sandi_salah(): void
    {
        $this->user(Role::Admin, 'admin.salah@example.test');

        $this->post(route('login.store'), [
            'email' => 'admin.salah@example.test',
            'password' => 'kata-sandi-salah',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_ditolak_jika_akun_tidak_aktif(): void
    {
        $this->user(Role::Admin, 'admin.nonaktif@example.test', false);

        $this->post(route('login.store'), [
            'email' => 'admin.nonaktif@example.test',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_command_reset_password_dapat_mengaktifkan_akun(): void
    {
        $user = $this->user(Role::Admin, 'admin.reset@example.test', false);

        $this->artisan('orcc4church:reset-password', [
            'email' => 'ADMIN.RESET@EXAMPLE.TEST',
            '--activate' => true,
        ])
            ->expectsQuestion('Kata sandi baru (minimal 8 karakter)', 'password-baru')
            ->expectsQuestion('Ulangi kata sandi baru', 'password-baru')
            ->assertSuccessful();

        $user->refresh();

        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check('password-baru', $user->password));
    }

    private function user(Role $role, string $email, bool $active = true): User
    {
        return User::query()->create([
            'name' => $role->label(),
            'email' => $email,
            'password' => 'password',
            'role' => $role,
            'is_active' => $active,
        ]);
    }
}

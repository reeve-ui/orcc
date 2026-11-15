<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use App\Services\UpdateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dapat_melihat_kartu_pembaruan_aplikasi(): void
    {
        $admin = $this->user(Role::Admin, 'admin.pembaruan@example.test');

        $this->mock(UpdateService::class, function ($mock): void {
            $mock->shouldReceive('status')->once()->andReturn($this->idleStatus());
            $mock->shouldReceive('lastCheck')->once()->andReturn($this->upToDateCheck());
        });

        $this->actingAs($admin)
            ->get(route('settings.edit'))
            ->assertOk()
            ->assertSee('Pembaruan aplikasi')
            ->assertSee('Cek pembaruan')
            ->assertSee('Update sekarang')
            ->assertSee('Commit terpasang')
            ->assertSee('Versi '.config('app.version'));
    }

    public function test_kartu_pembaruan_tidak_tampil_saat_fitur_dinonaktifkan(): void
    {
        config(['update.enabled' => false]);

        $admin = $this->user(Role::Admin, 'admin.pembaruan.nonaktif@example.test');

        $this->mock(UpdateService::class, function ($mock): void {
            $mock->shouldReceive('status')->once()->andReturn($this->idleStatus());
            $mock->shouldReceive('lastCheck')->once()->andReturn(null);
        });

        $this->actingAs($admin)
            ->get(route('settings.edit'))
            ->assertOk()
            ->assertDontSee('Pembaruan aplikasi');
    }

    public function test_selain_admin_tidak_dapat_mengakses_pembaruan(): void
    {
        $pengerja = $this->user(Role::Pengerja, 'pengerja.pembaruan@example.test');

        $this->actingAs($pengerja)
            ->post(route('settings.update.check'))
            ->assertForbidden();

        $this->actingAs($pengerja)
            ->post(route('settings.update.app'))
            ->assertForbidden();

        $this->actingAs($pengerja)
            ->get(route('settings.update.status'))
            ->assertForbidden();
    }

    public function test_admin_dapat_mengecek_pembaruan(): void
    {
        $admin = $this->user(Role::Admin, 'admin.cek.pembaruan@example.test');

        $this->mock(UpdateService::class, function ($mock): void {
            $mock->shouldReceive('check')
                ->once()
                ->with(true)
                ->andReturn(array_merge($this->upToDateCheck(), [
                    'state' => 'behind',
                    'update_available' => true,
                ]));
        });

        $this->actingAs($admin)
            ->post(route('settings.update.check'))
            ->assertRedirect(route('settings.edit'))
            ->assertSessionHas('status', 'Pembaruan tersedia dari GitHub.');
    }

    public function test_admin_dapat_memulai_pembaruan(): void
    {
        $admin = $this->user(Role::Admin, 'admin.mulai.pembaruan@example.test');

        $this->mock(UpdateService::class, function ($mock) use ($admin): void {
            $mock->shouldReceive('start')
                ->once()
                ->with($admin->id)
                ->andReturn([
                    'started' => true,
                    'message' => 'Proses pembaruan dimulai.',
                ]);
        });

        $this->actingAs($admin)
            ->post(route('settings.update.app'))
            ->assertRedirect(route('settings.edit'))
            ->assertSessionHas('status', 'Proses pembaruan dimulai.');
    }

    public function test_status_pembaruan_mengembalikan_json(): void
    {
        $admin = $this->user(Role::Admin, 'admin.status.pembaruan@example.test');

        $status = array_merge($this->idleStatus(), [
            'state' => 'running',
            'step' => 'composer',
            'message' => 'Memasang dependensi PHP...',
        ]);

        $this->mock(UpdateService::class, function ($mock) use ($status): void {
            $mock->shouldReceive('status')->once()->andReturn($status);
            $mock->shouldReceive('lastCheck')->once()->andReturn($this->upToDateCheck());
        });

        $this->actingAs($admin)
            ->getJson(route('settings.update.status'))
            ->assertOk()
            ->assertJsonPath('status.state', 'running')
            ->assertJsonPath('status.step', 'composer')
            ->assertJsonPath('check.state', 'up_to_date');
    }

    public function test_perintah_update_memanggil_service(): void
    {
        $this->mock(UpdateService::class, function ($mock): void {
            $mock->shouldReceive('run')->once()->with(5)->andReturn(0);
        });

        $this->artisan('orcc4church:update', ['--user' => 5])
            ->assertExitCode(0);
    }

    /**
     * @return array<string, mixed>
     */
    private function idleStatus(): array
    {
        return [
            'state' => 'idle',
            'step' => null,
            'message' => null,
            'output' => null,
            'warnings' => [],
            'started_at' => null,
            'finished_at' => null,
            'user_id' => null,
            'from_commit' => null,
            'to_commit' => null,
            'version' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function upToDateCheck(): array
    {
        return [
            'enabled' => true,
            'git' => true,
            'dirty' => false,
            'state' => 'up_to_date',
            'update_available' => false,
            'current_commit' => str_repeat('a', 40),
            'current_short' => 'aaaaaaa',
            'remote_commit' => str_repeat('a', 40),
            'remote_short' => 'aaaaaaa',
            'remote_url' => 'https://github.com/reeve-ui/orcc/commit/'.str_repeat('a', 40),
            'branch' => 'main',
            'repository' => 'reeve-ui/orcc',
            'version' => config('app.version'),
            'checked_at' => now()->toIso8601String(),
            'error' => null,
        ];
    }

    private function user(Role $role, string $email): User
    {
        return User::query()->create([
            'name' => $role->label(),
            'email' => $email,
            'password' => 'password',
            'role' => $role,
            'is_active' => true,
        ]);
    }
}

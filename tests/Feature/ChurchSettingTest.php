<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\ChurchSetting;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChurchSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dapat_melihat_dan_menyimpan_pengaturan_gereja(): void
    {
        $admin = $this->user(Role::Admin, 'admin.pengaturan@example.test');

        $this->actingAs($admin)
            ->get(route('settings.edit'))
            ->assertOk()
            ->assertSee('Pengaturan Gereja')
            ->assertSee('Nama Penanda Tangan')
            ->assertSee('Jabatan Penanda Tangan')
            ->assertSee('Reset data KKJ & jemaat')
            ->assertSee('RESET DATA')
            ->assertSee('Versi '.config('app.version'));

        $response = $this->actingAs($admin)->put(route('settings.update'), [
            'church_code' => 'sekrt',
            'name' => 'Gereja Uji',
            'tagline' => 'Data Uji',
            'address' => 'Depok, Jawa Barat',
            'phone' => '081200000000',
            'email' => 'gereja@example.test',
            'card_note' => 'Catatan kartu uji.',
            'card_signature_name' => 'Pdt. Uji, S.Th.',
            'card_signature_title' => 'Gembala Sidang Uji',
        ]);

        $response->assertRedirect(route('settings.edit'));
        $this->assertDatabaseHas('church_settings', [
            'id' => 1,
            'church_code' => 'SEKRT',
            'name' => 'Gereja Uji',
            'email' => 'gereja@example.test',
            'card_signature_name' => 'Pdt. Uji, S.Th.',
            'card_signature_title' => 'Gembala Sidang Uji',
        ]);
    }

    public function test_logo_png_diresize_otomatis_dan_disimpan_sebagai_png(): void
    {
        Storage::fake('public');

        $admin = $this->user(Role::Admin, 'admin.logo@example.test');

        $this->actingAs($admin)->put(route('settings.update'), [
            'church_code' => 'SEKRT',
            'name' => 'Gereja Uji',
            'address' => 'Depok',
            'logo' => UploadedFile::fake()->image('logo.png', 1600, 800),
        ])->assertRedirect(route('settings.edit'));

        $settings = ChurchSetting::current();
        $settings->refresh();

        $this->assertNotNull($settings->logo_path);
        $this->assertStringEndsWith('.png', $settings->logo_path);
        Storage::disk('public')->assertExists($settings->logo_path);

        $size = getimagesize(Storage::disk('public')->path($settings->logo_path));

        $this->assertIsArray($size);
        $this->assertSame(512, $size[0]);
        $this->assertSame(256, $size[1]);
    }

    public function test_logo_lama_dihapus_setelah_logo_baru_disimpan(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('church/logo-lama.png', 'logo lama');

        $settings = ChurchSetting::current();
        $settings->update(['logo_path' => 'church/logo-lama.png']);

        $admin = $this->user(Role::Admin, 'admin.logo.ganti@example.test');

        $this->actingAs($admin)->put(route('settings.update'), [
            'church_code' => 'SEKRT',
            'name' => 'Gereja Uji',
            'address' => 'Depok',
            'logo' => UploadedFile::fake()->image('logo-baru.png', 800, 800),
        ])->assertRedirect(route('settings.edit'));

        Storage::disk('public')->assertMissing('church/logo-lama.png');

        $settings->refresh();

        $this->assertNotSame('church/logo-lama.png', $settings->logo_path);
        Storage::disk('public')->assertExists($settings->logo_path);
    }

    public function test_selain_admin_tidak_dapat_mengakses_pengaturan(): void
    {
        $pengerja = $this->user(Role::Pengerja, 'pengerja.pengaturan@example.test');

        $this->actingAs($pengerja)
            ->get(route('settings.edit'))
            ->assertForbidden();

        $this->actingAs($pengerja)
            ->put(route('settings.update'), [
                'church_code' => 'SEKRT',
                'name' => 'Gereja Uji',
                'address' => 'Depok',
            ])
            ->assertForbidden();
    }

    public function test_tambah_kkj_diarahkan_ke_pengaturan_saat_kode_belum_diisi(): void
    {
        config(['church.code' => null]);

        $admin = $this->user(Role::Admin, 'admin.setup@example.test');

        $this->actingAs($admin)
            ->get(route('families.create'))
            ->assertRedirect(route('settings.edit'));
    }

    public function test_kode_gereja_terkunci_setelah_dipakai_nomor_kkj(): void
    {
        $settings = ChurchSetting::current();
        $settings->update(['church_code' => 'SEKRT']);

        Family::query()->create([
            'kkj_id' => 'SEKRT00260001',
            'head_name' => 'Keluarga Terkunci',
        ]);

        $admin = $this->user(Role::Admin, 'admin.kunci@example.test');

        $response = $this->actingAs($admin)->put(route('settings.update'), [
            'church_code' => 'ZZZZZ',
            'name' => 'Gereja Uji',
            'address' => 'Depok',
        ]);

        $response->assertRedirect(route('settings.edit'));
        $response->assertSessionHasErrors('church_code');
        $this->assertDatabaseHas('church_settings', [
            'id' => 1,
            'church_code' => 'SEKRT',
        ]);
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

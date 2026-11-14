<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Family;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FamilyAddressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['church.code' => 'SEKRT']);
        Carbon::setTestNow('2026-05-01 10:00:00');

        Region::query()->insert([
            'province' => 'Jawa Barat',
            'city' => 'Depok',
            'district' => 'Tapos',
            'village' => 'Sukamaju Baru',
            'postal_code' => '16455',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_alamat_berjenjang_valid_membuat_nomor_kkj_otomatis(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post(route('families.store'), [
            'head_name' => 'Keluarga Alamat Valid',
            'joined_year' => 2026,
            'province' => 'Jawa Barat',
            'city' => 'Depok',
            'district' => 'Tapos',
            'village' => 'Sukamaju Baru',
            'postal_code' => '16455',
            'address' => 'Jl. Uji No. 1',
        ]);

        $family = Family::query()->firstOrFail();

        $response->assertRedirect(route('families.show', $family));
        $this->assertSame('SEKRT00260001', $family->kkj_id);
        $this->assertDatabaseHas('families', [
            'head_name' => 'Keluarga Alamat Valid',
            'province' => 'Jawa Barat',
            'city' => 'Depok',
            'district' => 'Tapos',
            'village' => 'Sukamaju Baru',
            'postal_code' => '16455',
        ]);
    }

    public function test_kombinasi_wilayah_dan_kode_pos_harus_ada_di_data_wilayah(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post(route('families.store'), [
            'head_name' => 'Keluarga Alamat Salah',
            'province' => 'Jawa Barat',
            'city' => 'Depok',
            'district' => 'Tapos',
            'village' => 'Sukamaju Baru',
            'postal_code' => '16454',
            'address' => 'Jl. Uji No. 2',
        ]);

        $response->assertRedirect(route('families.create'));
        $response->assertSessionHasErrors('village');
        $this->assertDatabaseMissing('families', ['head_name' => 'Keluarga Alamat Salah']);
    }

    public function test_alamat_wajib_diisi(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post(route('families.store'), [
            'head_name' => 'Keluarga Tanpa Alamat',
        ]);

        $response->assertRedirect(route('families.create'));
        $response->assertSessionHasErrors([
            'province',
            'city',
            'district',
            'village',
            'postal_code',
            'address',
        ]);
    }

    private function admin(): User
    {
        return User::query()->create([
            'name' => 'Admin Alamat',
            'email' => 'admin.alamat@example.test',
            'password' => 'password',
            'role' => Role::Admin,
            'is_active' => true,
        ]);
    }
}

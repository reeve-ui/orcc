<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Enums\Role;
use App\Models\Family;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MemberFormTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_form_menampilkan_default_dan_atribut_wajib(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-07 10:00:00'));

        $family = $this->family();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('members.create', ['family_id' => $family->id]))
            ->assertOk()
            ->assertSee("x-data='memberForm(", false)
            ->assertSee('x-show="isAnggota"', false)
            ->assertSee('x-show="isMarried"', false)
            ->assertSee('x-bind:disabled="!isMarried"', false)
            ->assertSee('name="gender" required', false)
            ->assertSee('name="marital_status" required', false)
            ->assertSee('name="birth_date" type="date" required min="1927-11-07" max="2026-11-07"', false)
            ->assertSee('<option value="Pria" selected>Pria</option>', false)
            ->assertSee('<option value="Belum Menikah" selected>Belum Menikah</option>', false)
            ->assertSee('<option value="Telah Menikah"', false)
            ->assertSee('placeholder="SEKRT0026000100"', false);
    }

    public function test_kepala_keluarga_membuat_ktj_0_dan_gender_pria(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-07 10:00:00'));

        $family = $this->family();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('members.store'), $this->payload($family, [
                'family_status' => 'Kepala Keluarga',
                'gender' => 'Pria',
                'marital_status' => 'Telah Menikah',
            ]))
            ->assertRedirect(route('families.show', $family));

        $this->assertDatabaseHas('members', [
            'family_id' => $family->id,
            'full_name' => 'Jemaat Uji',
            'family_status' => 'Kepala Keluarga',
            'child_order' => null,
            'gender' => 'Pria',
            'ktj_id' => 'SEKRT0026000100',
        ]);
    }

    public function test_pasangan_wanita_membuat_ktj_0_dan_gender_1(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-07 10:00:00'));

        $family = $this->family();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('members.store'), $this->payload($family, [
                'family_status' => 'Pasangan',
                'gender' => 'Wanita',
                'marital_status' => 'Telah Menikah',
            ]))
            ->assertRedirect(route('families.show', $family));

        $this->assertDatabaseHas('members', [
            'family_id' => $family->id,
            'family_status' => 'Pasangan',
            'gender' => 'Wanita',
            'ktj_id' => 'SEKRT0026000101',
        ]);
    }

    public function test_anggota_keluarga_wajib_mengisi_anak_ke(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-07 10:00:00'));

        $family = $this->family();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('members.store'), $this->payload($family, [
                'family_status' => 'Anggota Keluarga',
                'gender' => 'Pria',
                'marital_status' => 'Belum Menikah',
                'child_order' => null,
            ]))
            ->assertSessionHasErrors('child_order');

        $this->assertDatabaseCount('members', 0);
    }

    public function test_anggota_keluarga_membuat_ktj_sesuai_anak_ke_dan_gender(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-07 10:00:00'));

        $family = $this->family();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('members.store'), $this->payload($family, [
                'family_status' => 'Anggota Keluarga',
                'gender' => 'Wanita',
                'marital_status' => 'Belum Menikah',
                'child_order' => 2,
            ]))
            ->assertRedirect(route('families.show', $family));

        $this->assertDatabaseHas('members', [
            'family_id' => $family->id,
            'family_status' => 'Anggota Keluarga',
            'child_order' => 2,
            'gender' => 'Wanita',
            'ktj_id' => 'SEKRT0026000121',
        ]);
    }

    public function test_anak_ke_maksimal_9(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-07 10:00:00'));

        $family = $this->family();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('members.store'), $this->payload($family, [
                'family_status' => 'Anggota Keluarga',
                'gender' => 'Pria',
                'marital_status' => 'Belum Menikah',
                'child_order' => 10,
            ]))
            ->assertSessionHasErrors('child_order');

        $this->assertDatabaseCount('members', 0);
    }

    public function test_jenis_kelamin_wajib_diisi(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-07 10:00:00'));

        $family = $this->family();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('members.store'), $this->payload($family, [
                'family_status' => 'Kepala Keluarga',
                'gender' => null,
                'marital_status' => 'Telah Menikah',
            ]))
            ->assertSessionHasErrors('gender');

        $this->assertDatabaseCount('members', 0);
    }

    public function test_status_pernikahan_wajib_diisi(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-07 10:00:00'));

        $family = $this->family();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('members.store'), $this->payload($family, [
                'family_status' => 'Kepala Keluarga',
                'gender' => 'Pria',
                'marital_status' => null,
            ]))
            ->assertSessionHasErrors('marital_status');

        $this->assertDatabaseCount('members', 0);
    }

    public function test_tanggal_lahir_wajib_dan_dibatasi_99_tahun(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-07 10:00:00'));

        $family = $this->family();
        $admin = $this->admin();

        $base = [
            'family_status' => 'Kepala Keluarga',
            'gender' => 'Pria',
            'marital_status' => 'Telah Menikah',
        ];

        $this->actingAs($admin)
            ->post(route('members.store'), $this->payload($family, $base + ['birth_date' => null]))
            ->assertSessionHasErrors('birth_date');

        $this->actingAs($admin)
            ->post(route('members.store'), $this->payload($family, $base + ['birth_date' => '2026-11-08']))
            ->assertSessionHasErrors('birth_date');

        $this->actingAs($admin)
            ->post(route('members.store'), $this->payload($family, $base + ['birth_date' => '1927-11-06']))
            ->assertSessionHasErrors('birth_date');

        $this->actingAs($admin)
            ->post(route('members.store'), $this->payload($family, $base + ['birth_date' => '1927-11-07']))
            ->assertRedirect(route('families.show', $family));

        $this->assertDatabaseHas('members', [
            'family_id' => $family->id,
        ]);

        $this->assertSame(
            '1927-11-07',
            Member::query()->where('family_id', $family->id)->firstOrFail()->birth_date?->toDateString()
        );
    }

    public function test_ktj_lama_dipertahankan_saat_edit_tidak_mengubah_sumber_ktj(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-07 10:00:00'));

        $family = $this->family();
        $admin = $this->admin();
        $member = $this->member($family, [
            'ktj_id' => 'LEGACY-001',
            'family_status' => 'Kepala Keluarga',
            'gender' => Gender::Pria,
        ]);

        $this->actingAs($admin)
            ->put(route('members.update', $member), $this->payload($family, [
                'full_name' => 'Nama Diperbarui',
                'family_status' => 'Kepala Keluarga',
                'gender' => 'Pria',
                'marital_status' => 'Telah Menikah',
            ]))
            ->assertRedirect(route('members.show', $member));

        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'full_name' => 'Nama Diperbarui',
            'ktj_id' => 'LEGACY-001',
        ]);
    }

    public function test_ktj_diperbarui_saat_jenis_kelamin_diubah(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-07 10:00:00'));

        $family = $this->family();
        $admin = $this->admin();
        $member = $this->member($family, [
            'ktj_id' => 'LEGACY-001',
            'family_status' => 'Kepala Keluarga',
            'gender' => Gender::Pria,
        ]);

        $this->actingAs($admin)
            ->put(route('members.update', $member), $this->payload($family, [
                'family_status' => 'Kepala Keluarga',
                'gender' => 'Wanita',
                'marital_status' => 'Telah Menikah',
            ]))
            ->assertRedirect(route('members.show', $member));

        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'gender' => 'Wanita',
            'ktj_id' => 'SEKRT0026000101',
        ]);
    }

    public function test_nama_pasangan_disimpan_saat_status_telah_menikah(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-07 10:00:00'));

        $family = $this->family();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('members.store'), $this->payload($family, [
                'family_status' => 'Kepala Keluarga',
                'gender' => 'Pria',
                'marital_status' => 'Telah Menikah',
                'spouse_name' => 'Pasangan Uji',
            ]))
            ->assertRedirect(route('families.show', $family));

        $this->assertDatabaseHas('members', [
            'family_id' => $family->id,
            'marital_status' => 'Telah Menikah',
            'spouse_name' => 'Pasangan Uji',
        ]);
    }

    public function test_nama_pasangan_dikosongkan_saat_status_bukan_telah_menikah(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-07 10:00:00'));

        $family = $this->family();
        $admin = $this->admin();
        $member = $this->member($family, [
            'family_status' => 'Kepala Keluarga',
            'gender' => Gender::Pria,
            'marital_status' => 'Telah Menikah',
            'spouse_name' => 'Pasangan Lama',
        ]);

        $this->actingAs($admin)
            ->put(route('members.update', $member), $this->payload($family, [
                'family_status' => 'Kepala Keluarga',
                'gender' => 'Pria',
                'marital_status' => 'Belum Menikah',
                'spouse_name' => 'Pasangan Lama',
            ]))
            ->assertRedirect(route('members.show', $member));

        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'marital_status' => 'Belum Menikah',
            'spouse_name' => null,
        ]);
    }

    private function family(): Family
    {
        return Family::query()->create([
            'kkj_id' => 'SEKRT00260001',
            'head_name' => 'Kepala Keluarga Uji',
        ]);
    }

    private function member(Family $family, array $attributes = []): Member
    {
        return Member::query()->create($attributes + [
            'family_id' => $family->id,
            'full_name' => 'Jemaat Uji',
            'birth_date' => '1980-01-01',
            'marital_status' => 'Telah Menikah',
        ]);
    }

    private function admin(): User
    {
        return User::query()->create([
            'name' => 'Admin',
            'email' => 'admin.jemaat@example.test',
            'password' => 'password',
            'role' => Role::Admin,
            'is_active' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(Family $family, array $overrides = []): array
    {
        return $overrides + [
            'family_id' => $family->id,
            'full_name' => 'Jemaat Uji',
            'birth_date' => '1980-01-01',
            'marital_status' => 'Belum Menikah',
            'gender' => 'Pria',
            'family_status' => 'Kepala Keluarga',
        ];
    }
}

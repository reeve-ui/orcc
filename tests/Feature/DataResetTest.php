<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Enums\Role;
use App\Models\ChurchSetting;
use App\Models\Family;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DataResetTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_admin_dapat_mereset_seluruh_data_kkj_dan_jemaat(): void
    {
        Storage::fake('public');
        Carbon::setTestNow('2026-05-01 10:00:00');
        config(['church.code' => 'SEKRT']);

        $admin = $this->user(Role::Admin, 'admin.reset@example.test');
        $family = Family::query()->create([
            'kkj_id' => 'SEKRT00260001',
            'head_name' => 'Keluarga Lama',
            'photo_path' => 'family-photos/keluarga.jpg',
        ]);

        $member = Member::query()->create([
            'family_id' => $family->id,
            'full_name' => 'Jemaat Lama',
            'family_status' => 'Kepala Keluarga',
            'gender' => Gender::Pria,
            'photo_path' => 'member-photos/jemaat.jpg',
        ]);

        $linkedUser = $this->user(Role::Jemaat, 'jemaat.reset@example.test', $member->id);

        Storage::disk('public')->put('family-photos/keluarga.jpg', 'foto keluarga');
        Storage::disk('public')->put('member-photos/jemaat.jpg', 'foto jemaat');

        DB::table('kkj_sequences')->insert([
            'sequence_year' => 2026,
            'last_number' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $settingsBefore = ChurchSetting::current()->only(['name', 'church_code']);

        $response = $this->actingAs($admin)->delete(route('settings.reset-data'), [
            'confirmation' => 'reset data',
            'confirm_reset' => '1',
        ]);

        $response->assertRedirect(route('settings.edit'));
        $response->assertSessionHas('status', 'Reset data selesai. 1 KKJ dan 1 jemaat dihapus.');

        $this->assertDatabaseCount('families', 0);
        $this->assertDatabaseCount('members', 0);
        $this->assertDatabaseCount('kkj_sequences', 0);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
        $this->assertDatabaseHas('users', ['id' => $linkedUser->id, 'member_id' => null]);
        $this->assertSame($settingsBefore, ChurchSetting::current()->only(['name', 'church_code']));

        Storage::disk('public')->assertMissing('family-photos/keluarga.jpg');
        Storage::disk('public')->assertMissing('member-photos/jemaat.jpg');

        $newFamily = Family::query()->create(['head_name' => 'Keluarga Baru']);

        $this->assertSame('SEKRT00260001', $newFamily->kkj_id);
    }

    public function test_selain_admin_tidak_dapat_mereset_data(): void
    {
        $leader = $this->user(Role::Leader, 'leader.reset@example.test');

        Family::query()->create([
            'kkj_id' => 'SEKRT00260001',
            'head_name' => 'Keluarga Tetap',
        ]);

        $this->actingAs($leader)
            ->delete(route('settings.reset-data'), [
                'confirmation' => 'RESET DATA',
                'confirm_reset' => '1',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('families', 1);
        $this->assertDatabaseHas('families', ['head_name' => 'Keluarga Tetap']);
    }

    public function test_reset_data_memerlukan_konfirmasi_teks_dan_centang(): void
    {
        $admin = $this->user(Role::Admin, 'admin.konfirmasi@example.test');

        $family = Family::query()->create([
            'kkj_id' => 'SEKRT00260001',
            'head_name' => 'Keluarga Aman',
        ]);

        Member::query()->create([
            'family_id' => $family->id,
            'full_name' => 'Jemaat Aman',
            'family_status' => 'Kepala Keluarga',
        ]);

        $this->actingAs($admin)
            ->delete(route('settings.reset-data'), [
                'confirmation' => 'HAPUS',
                'confirm_reset' => '1',
            ])
            ->assertRedirect(route('settings.edit'))
            ->assertSessionHasErrors('confirmation');

        $this->assertDatabaseCount('families', 1);
        $this->assertDatabaseCount('members', 1);

        $this->actingAs($admin)
            ->delete(route('settings.reset-data'), [
                'confirmation' => 'RESET DATA',
            ])
            ->assertRedirect(route('settings.edit'))
            ->assertSessionHasErrors('confirm_reset');

        $this->assertDatabaseCount('families', 1);
        $this->assertDatabaseCount('members', 1);
    }

    private function user(Role $role, string $email, ?int $memberId = null): User
    {
        return User::query()->create([
            'name' => $role->label(),
            'email' => $email,
            'password' => 'password',
            'role' => $role,
            'member_id' => $memberId,
            'is_active' => true,
        ]);
    }
}

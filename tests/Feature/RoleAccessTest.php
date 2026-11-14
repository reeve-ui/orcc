<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Enums\Role;
use App\Models\Family;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_jemaat_can_only_view_own_family(): void
    {
        $ownFamily = Family::query()->create([
            'kkj_id' => 'TEST-OWN',
            'head_name' => 'Keluarga Sendiri',
        ]);

        $otherFamily = Family::query()->create([
            'kkj_id' => 'TEST-OTHER',
            'head_name' => 'Keluarga Lain',
        ]);

        $member = Member::query()->create([
            'family_id' => $ownFamily->id,
            'full_name' => 'Anggota Sendiri',
            'family_status' => 'Kepala Keluarga',
            'gender' => Gender::Pria,
        ]);

        $user = User::query()->create([
            'name' => 'Jemaat',
            'email' => 'jemaat@example.test',
            'password' => 'password',
            'role' => Role::Jemaat,
            'member_id' => $member->id,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('families.show', $ownFamily))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('families.show', $otherFamily))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('members.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_pengerja_can_view_member_list_but_cannot_delete(): void
    {
        $family = Family::query()->create([
            'kkj_id' => 'TEST-STAFF',
            'head_name' => 'Keluarga Staff',
        ]);

        $member = Member::query()->create([
            'family_id' => $family->id,
            'full_name' => 'Anggota Staff',
            'family_status' => 'Kepala Keluarga',
        ]);

        $user = User::query()->create([
            'name' => 'Pengerja',
            'email' => 'pengerja@example.test',
            'password' => 'password',
            'role' => Role::Pengerja,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('members.index'))
            ->assertOk();

        $this->actingAs($user)
            ->delete(route('members.destroy', $member))
            ->assertForbidden();
    }

    public function test_admin_can_manage_users(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'password',
            'role' => Role::Admin,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Pengerja Baru',
                'email' => 'pengerja.baru@example.test',
                'role' => Role::Pengerja->value,
                'is_active' => true,
                'password' => 'rahasia123',
                'password_confirmation' => 'rahasia123',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'pengerja.baru@example.test',
            'role' => Role::Pengerja->value,
        ]);
    }

    public function test_leader_can_delete_but_cannot_manage_users(): void
    {
        $family = Family::query()->create([
            'kkj_id' => 'TEST-LEADER',
            'head_name' => 'Keluarga Leader',
        ]);

        $member = Member::query()->create([
            'family_id' => $family->id,
            'full_name' => 'Anggota Leader',
            'family_status' => 'Kepala Keluarga',
        ]);

        $leader = User::query()->create([
            'name' => 'Leader',
            'email' => 'leader@example.test',
            'password' => 'password',
            'role' => Role::Leader,
            'is_active' => true,
        ]);

        $this->actingAs($leader)
            ->delete(route('members.destroy', $member))
            ->assertRedirect(route('families.show', $family->id));

        $this->assertDatabaseMissing('members', ['id' => $member->id]);

        $this->actingAs($leader)
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_jemaat_cannot_view_another_family_member_profile(): void
    {
        $family = Family::query()->create([
            'kkj_id' => 'TEST-PRIVACY',
            'head_name' => 'Keluarga Privasi',
        ]);

        $self = Member::query()->create([
            'family_id' => $family->id,
            'full_name' => 'Anggota Sendiri',
            'family_status' => 'Kepala Keluarga',
        ]);

        $other = Member::query()->create([
            'family_id' => $family->id,
            'full_name' => 'Anggota Lain',
            'family_status' => 'Anak',
        ]);

        $user = User::query()->create([
            'name' => 'Jemaat',
            'email' => 'privasi@example.test',
            'password' => 'password',
            'role' => Role::Jemaat,
            'member_id' => $self->id,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('members.show', $self))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('members.show', $other))
            ->assertForbidden();
    }
}

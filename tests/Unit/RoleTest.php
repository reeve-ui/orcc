<?php

namespace Tests\Unit;

use App\Enums\Role;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    public function test_role_hierarchy_and_permissions(): void
    {
        $this->assertTrue(Role::Admin->canManageUsers());
        $this->assertFalse(Role::Leader->canManageUsers());

        $this->assertTrue(Role::Pengerja->canManageData());
        $this->assertFalse(Role::Jemaat->canManageData());

        $this->assertTrue(Role::Leader->canDeleteData());
        $this->assertFalse(Role::Pengerja->canDeleteData());

        $this->assertTrue(Role::Jemaat->isStaff() === false);
        $this->assertTrue(Role::Pengerja->isStaff());
    }

    public function test_options_are_ordered_from_highest_to_lowest_access(): void
    {
        $this->assertSame([
            1 => 'Admin',
            2 => 'Leader',
            3 => 'Pengerja',
            4 => 'Jemaat',
        ], Role::options());
    }
}

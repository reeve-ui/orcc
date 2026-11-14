<?php

namespace App\Policies;

use App\Models\Family;
use App\Models\User;

class FamilyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Family $family): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return $user->member !== null && $user->member->family_id === $family->id;
    }

    public function create(User $user): bool
    {
        return $user->role->canManageData();
    }

    public function update(User $user, Family $family): bool
    {
        return $user->role->canManageData();
    }

    public function delete(User $user, Family $family): bool
    {
        return $user->role->canDeleteData();
    }
}

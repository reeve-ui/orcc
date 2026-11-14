<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\User;

class MemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Member $member): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return $user->member !== null && $user->member->id === $member->id;
    }

    public function create(User $user): bool
    {
        return $user->role->canManageData();
    }

    public function update(User $user, Member $member): bool
    {
        return $user->role->canManageData();
    }

    public function delete(User $user, Member $member): bool
    {
        return $user->role->canDeleteData();
    }
}

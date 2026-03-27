<?php

namespace App\Policies;

use App\Models\LicensePlan;
use App\Models\User;

class LicensePlanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->canAccessAdminPanel();
    }

    public function view(User $user, LicensePlan $licensePlan): bool
    {
        return $user->role->canAccessAdminPanel();
    }

    public function create(User $user): bool
    {
        return $user->role->canManageUsers();
    }

    public function update(User $user, LicensePlan $licensePlan): bool
    {
        return $user->role->canManageUsers();
    }
}

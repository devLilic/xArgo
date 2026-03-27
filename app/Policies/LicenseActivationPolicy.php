<?php

namespace App\Policies;

use App\Models\LicenseActivation;
use App\Models\User;

class LicenseActivationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->canAccessAdminPanel();
    }

    public function view(User $user, LicenseActivation $activation): bool
    {
        return $user->role->canAccessAdminPanel();
    }

    public function rebind(User $user, LicenseActivation $activation): bool
    {
        return $user->role->canManageUsers();
    }
}

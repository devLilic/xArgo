<?php

namespace App\Policies;

use App\Models\LicenseHeartbeat;
use App\Models\User;

class LicenseHeartbeatPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->canAccessAdminPanel();
    }

    public function view(User $user, LicenseHeartbeat $heartbeat): bool
    {
        return $user->role->canAccessAdminPanel();
    }
}

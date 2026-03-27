<?php

namespace App\Policies;

use App\Models\User;

class UserInvitationPolicy
{
    public function create(User $user): bool
    {
        return $user->role->canManageInvitations();
    }
}

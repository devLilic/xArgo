<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAdminPanel(User $user): bool
    {
        return $user->role->canAccessAdminPanel();
    }
}

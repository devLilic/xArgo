<?php

namespace App\Policies;

use App\Models\App;
use App\Models\User;

class AppPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->canAccessAdminPanel();
    }

    public function view(User $user, App $app): bool
    {
        return $user->role->canAccessAdminPanel();
    }

    public function create(User $user): bool
    {
        return $user->role->canManageUsers();
    }

    public function update(User $user, App $app): bool
    {
        return $user->role->canManageUsers();
    }
}

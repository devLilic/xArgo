<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->canViewUsers();
    }

    public function viewAdminPanel(User $user): bool
    {
        return $user->role->canAccessAdminPanel();
    }

    public function view(User $user, User $managedUser): bool
    {
        return $user->role->canViewUsers();
    }

    public function updateRole(User $user, User $managedUser): bool
    {
        return $user->role->canManageUsers() && $user->isNot($managedUser);
    }

    public function toggleActive(User $user, User $managedUser): bool
    {
        return $user->role->canManageUsers() && $user->isNot($managedUser);
    }
}

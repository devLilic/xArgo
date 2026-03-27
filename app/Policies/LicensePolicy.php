<?php

namespace App\Policies;

use App\Models\License;
use App\Models\User;

class LicensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->canAccessAdminPanel();
    }

    public function view(User $user, License $license): bool
    {
        return $user->role->canAccessAdminPanel();
    }

    public function create(User $user): bool
    {
        return $user->role->canManageUsers();
    }

    public function export(User $user): bool
    {
        return $user->role->canExportLicenses();
    }

    public function update(User $user, License $license): bool
    {
        return $user->role->canManageUsers();
    }

    public function changeStatus(User $user, License $license): bool
    {
        return $user->role->canManageUsers();
    }

    public function delete(User $user, License $license): bool
    {
        return $user->role->canManageUsers();
    }

    public function restore(User $user, License $license): bool
    {
        return $user->role->canManageUsers();
    }
}

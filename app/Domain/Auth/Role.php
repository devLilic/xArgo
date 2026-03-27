<?php

namespace App\Domain\Auth;

enum Role: string
{
    case SUPER_ADMIN = 'super_admin';
    case SUPPORT = 'support';
    case READ_ONLY = 'read_only';

    public function canManageInvitations(): bool
    {
        return match ($this) {
            self::SUPER_ADMIN, self::SUPPORT => true,
            self::READ_ONLY => false,
        };
    }

    public function canAccessAdminPanel(): bool
    {
        return true;
    }

    public function canViewUsers(): bool
    {
        return true;
    }

    public function canManageUsers(): bool
    {
        return $this === self::SUPER_ADMIN;
    }
}

<?php

namespace App\Domain\Licensing;

enum LicenseActivationStatus: string
{
    case ACTIVE = 'active';
    case BLOCKED = 'blocked';
    case INACTIVE = 'inactive';
    case STALE = 'stale';
}

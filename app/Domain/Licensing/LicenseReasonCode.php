<?php

namespace App\Domain\Licensing;

class LicenseReasonCode
{
    public const DEVICE_MISMATCH = 'device_mismatch';
    public const VALIDATION_FAILED = 'validation_failed';
    public const REBIND_PENDING_MANUAL_CONFIRMATION = 'rebind_pending_manual_confirmation';
    public const INTERNAL_ERROR = 'internal_error';

    public static function forLicenseStatus(LicenseStatus $status): string
    {
        return 'license_'.$status->value;
    }
}

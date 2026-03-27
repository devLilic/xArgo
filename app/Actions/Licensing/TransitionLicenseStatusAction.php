<?php

namespace App\Actions\Licensing;

use App\Domain\Licensing\LicenseStatus;
use App\Models\License;
use InvalidArgumentException;

class TransitionLicenseStatusAction
{
    public function execute(License $license, string $action): License
    {
        $status = match ($action) {
            'suspend' => LicenseStatus::SUSPENDED,
            'revoke' => LicenseStatus::REVOKED,
            'reactivate' => LicenseStatus::ACTIVE,
            default => throw new InvalidArgumentException('Unsupported license status action.'),
        };

        $license->update([
            'status' => $status,
        ]);

        return $license->fresh();
    }
}

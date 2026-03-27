<?php

namespace App\Actions\Licensing;

use App\Models\LicenseHeartbeat;
use Carbon\CarbonInterface;

class PruneLicenseHeartbeatsAction
{
    public function execute(?CarbonInterface $referenceTime = null): int
    {
        $cutoff = ($referenceTime ?? now())
            ->copy()
            ->subDays((int) config('licensing.devices.heartbeat_retention_days'));

        return LicenseHeartbeat::query()
            ->where('received_at', '<', $cutoff)
            ->delete();
    }
}

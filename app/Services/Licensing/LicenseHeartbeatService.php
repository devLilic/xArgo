<?php

namespace App\Services\Licensing;

use App\Models\LicenseActivation;
use App\Models\LicenseHeartbeat;
use Carbon\CarbonInterface;

class LicenseHeartbeatService
{
    public function recordHeartbeat(
        LicenseActivation $activation,
        string $appVersion,
        ?string $ipAddress = null,
        ?string $reasonCode = null,
        ?CarbonInterface $receivedAt = null,
    ): LicenseHeartbeat {
        $timestamp = $receivedAt ?? now();

        $heartbeat = $activation->heartbeats()->create([
            'app_version' => $appVersion,
            'received_at' => $timestamp,
            'ip_address' => $ipAddress,
            'reason_code' => $reasonCode,
        ]);

        $activation->update([
            'last_seen_at' => $timestamp,
            'last_reason_code' => $reasonCode,
        ]);

        return $heartbeat->fresh();
    }
}

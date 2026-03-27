<?php

namespace App\Services\Licensing;

use App\Models\LicenseActivation;
use App\Support\Licensing\AntiCloneDecision;
use Carbon\CarbonInterface;

class AntiClonePolicyService
{
    public function evaluate(
        LicenseActivation $activation,
        string $machineId,
        string $installationId,
        ?CarbonInterface $now = null,
    ): AntiCloneDecision {
        if ($activation->matchesDevice($machineId, $installationId)) {
            return new AntiCloneDecision(
                allowed: true,
                matchesBoundDevice: true,
                blocked: false,
                reasonCode: null,
                graceUntil: null,
            );
        }

        $evaluatedAt = $now ?? now();
        $reasonCode = (string) config('licensing.device_mismatch.block_reason_code');
        $configuredGraceUntil = $activation->grace_until;

        if ($configuredGraceUntil === null) {
            return new AntiCloneDecision(
                allowed: true,
                matchesBoundDevice: false,
                blocked: false,
                reasonCode: $reasonCode,
                graceUntil: $evaluatedAt->copy()->addSeconds(
                    (int) config('licensing.device_mismatch.grace_period_seconds')
                ),
            );
        }

        if ($configuredGraceUntil->greaterThan($evaluatedAt)) {
            return new AntiCloneDecision(
                allowed: true,
                matchesBoundDevice: false,
                blocked: false,
                reasonCode: $reasonCode,
                graceUntil: $configuredGraceUntil,
            );
        }

        return new AntiCloneDecision(
            allowed: false,
            matchesBoundDevice: false,
            blocked: true,
            reasonCode: $reasonCode,
            graceUntil: $configuredGraceUntil,
        );
    }
}

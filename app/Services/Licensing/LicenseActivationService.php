<?php

namespace App\Services\Licensing;

use App\Domain\Licensing\LicenseActivationStatus;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Support\Licensing\FirstActivationResult;
use App\Support\Licensing\IssuedActivationToken;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

class LicenseActivationService
{
    public function activateFirstDevice(
        License $license,
        string $machineId,
        string $installationId,
        ?string $deviceLabel = null,
        ?CarbonInterface $seenAt = null,
    ): FirstActivationResult {
        $timestamp = $seenAt ?? now();
        $issuedToken = $this->issueActivationToken();

        $activation = LicenseActivation::query()->create([
            'license_id' => $license->id,
            'activation_id' => (string) Str::uuid(),
            'machine_id' => $machineId,
            'installation_id' => $installationId,
            'activation_token_hash' => $issuedToken->hashedToken,
            'device_label' => $deviceLabel,
            'status' => LicenseActivationStatus::ACTIVE,
            'first_seen_at' => $timestamp,
            'last_seen_at' => $timestamp,
            'grace_until' => null,
            'last_reason_code' => null,
        ]);

        return new FirstActivationResult(
            activation: $activation,
            plainTextToken: $issuedToken->plainTextToken,
        );
    }

    public function findActivation(
        License $license,
        ?string $activationId = null,
        ?string $machineId = null,
        ?string $installationId = null,
    ): ?LicenseActivation {
        return LicenseActivation::query()
            ->where('license_id', $license->id)
            ->when($activationId !== null, fn ($query) => $query->where('activation_id', $activationId))
            ->when(
                $activationId === null && $machineId !== null && $installationId !== null,
                fn ($query) => $query
                    ->where('machine_id', $machineId)
                    ->where('installation_id', $installationId),
            )
            ->first();
    }

    public function countActiveDevices(License $license): int
    {
        return LicenseActivation::query()
            ->where('license_id', $license->id)
            ->where('status', LicenseActivationStatus::ACTIVE)
            ->count();
    }

    public function issueActivationToken(): IssuedActivationToken
    {
        $plainTextToken = Str::random(80);

        return new IssuedActivationToken(
            plainTextToken: $plainTextToken,
            hashedToken: hash('sha256', $plainTextToken),
        );
    }
}

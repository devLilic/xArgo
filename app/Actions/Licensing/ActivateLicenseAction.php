<?php

namespace App\Actions\Licensing;

use App\Domain\Licensing\LicenseReasonCode;
use App\Domain\Licensing\LicenseStatus;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Services\Licensing\AntiClonePolicyService;
use App\Services\Licensing\LicenseActivationService;
use App\Support\Licensing\ActivateLicenseResult;
use Illuminate\Validation\ValidationException;

class ActivateLicenseAction
{
    public function __construct(
        private readonly LicenseActivationService $activationService,
        private readonly AntiClonePolicyService $antiClonePolicy,
    ) {
    }

    /**
     * @param  array{licenseKey:string,appId:string,appVersion:string,machineId:string,installationId:string}  $payload
     */
    public function execute(array $payload): ActivateLicenseResult
    {
        $license = License::query()
            ->with(['app', 'entitlements', 'activations'])
            ->where('license_key', $payload['licenseKey'])
            ->whereHas('app', fn ($query) => $query->where('app_id', $payload['appId']))
            ->first();

        if ($license === null) {
            throw ValidationException::withMessages([
                'licenseKey' => 'The provided license could not be activated for this application.',
            ]);
        }

        if ($license->status !== LicenseStatus::ACTIVE) {
            return new ActivateLicenseResult(
                activationId: null,
                activationToken: null,
                licenseStatus: $license->status->value,
                graceUntil: null,
                entitlements: $this->presentEntitlements($license),
                reasonCode: LicenseReasonCode::forLicenseStatus($license->status),
            );
        }

        $existingActivation = $this->activationService->findActivation(
            $license,
            machineId: $payload['machineId'],
            installationId: $payload['installationId'],
        );

        if ($existingActivation !== null) {
            $issuedToken = $this->activationService->issueActivationToken();

            $existingActivation->update([
                'activation_token_hash' => $issuedToken->hashedToken,
                'last_seen_at' => now(),
                'last_reason_code' => null,
            ]);

            $license->update([
                'last_validated_at' => now(),
            ]);

            return new ActivateLicenseResult(
                activationId: $existingActivation->activation_id,
                activationToken: $issuedToken->plainTextToken,
                licenseStatus: $license->status->value,
                graceUntil: $existingActivation->grace_until?->toIso8601String(),
                entitlements: $this->presentEntitlements($license),
                reasonCode: null,
            );
        }

        if ($this->activationService->countActiveDevices($license) < $license->max_devices) {
            $firstActivation = $this->activationService->activateFirstDevice(
                license: $license,
                machineId: $payload['machineId'],
                installationId: $payload['installationId'],
            );

            $license->update([
                'last_validated_at' => now(),
            ]);

            return new ActivateLicenseResult(
                activationId: $firstActivation->activation->activation_id,
                activationToken: $firstActivation->plainTextToken,
                licenseStatus: $license->status->value,
                graceUntil: null,
                entitlements: $this->presentEntitlements($license),
                reasonCode: null,
            );
        }

        /** @var LicenseActivation $boundActivation */
        $boundActivation = $license->activations()
            ->whereNull('deleted_at')
            ->orderByDesc('last_seen_at')
            ->orderByDesc('id')
            ->firstOrFail();

        $decision = $this->antiClonePolicy->evaluate(
            $boundActivation,
            $payload['machineId'],
            $payload['installationId'],
        );

        $boundActivation->update([
            'grace_until' => $decision->graceUntil,
            'last_reason_code' => $decision->reasonCode,
        ]);

        return new ActivateLicenseResult(
            activationId: $boundActivation->activation_id,
            activationToken: null,
            licenseStatus: $license->status->value,
            graceUntil: $decision->graceUntil?->toIso8601String(),
            entitlements: $this->presentEntitlements($license),
            reasonCode: $decision->reasonCode,
        );
    }

    /**
     * @return array<int, array{featureCode:string, enabled:bool}>
     */
    private function presentEntitlements(License $license): array
    {
        return $license->entitlements
            ->map(fn ($entitlement): array => [
                'featureCode' => $entitlement->feature_code,
                'enabled' => $entitlement->enabled,
            ])
            ->values()
            ->all();
    }
}

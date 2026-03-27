<?php

namespace App\Actions\Licensing;

use App\Domain\Licensing\LicenseReasonCode;
use App\Domain\Licensing\LicenseStatus;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Services\Licensing\AntiClonePolicyService;
use App\Support\Licensing\ValidateLicenseResult;
use Illuminate\Validation\ValidationException;

class ValidateLicenseAction
{
    public function __construct(
        private readonly AntiClonePolicyService $antiClonePolicy,
    ) {
    }

    /**
     * @param  array{licenseKey:string,activationToken:string,appId:string,appVersion:string,machineId:string,installationId:string}  $payload
     */
    public function execute(array $payload): ValidateLicenseResult
    {
        $license = License::query()
            ->with(['app', 'entitlements'])
            ->where('license_key', $payload['licenseKey'])
            ->whereHas('app', fn ($query) => $query->where('app_id', $payload['appId']))
            ->first();

        if ($license === null) {
            throw ValidationException::withMessages([
                'licenseKey' => 'The provided license could not be validated for this application.',
            ]);
        }

        if ($license->status !== LicenseStatus::ACTIVE) {
            return new ValidateLicenseResult(
                isValid: false,
                activationId: null,
                licenseStatus: $license->status->value,
                graceUntil: null,
                entitlements: $this->presentEntitlements($license),
                reasonCode: LicenseReasonCode::forLicenseStatus($license->status),
            );
        }

        $activation = LicenseActivation::query()
            ->where('license_id', $license->id)
            ->where('activation_token_hash', hash('sha256', $payload['activationToken']))
            ->first();

        if ($activation === null) {
            throw ValidationException::withMessages([
                'activationToken' => 'The provided activation token is invalid for this license.',
            ]);
        }

        $decision = $this->antiClonePolicy->evaluate(
            $activation,
            $payload['machineId'],
            $payload['installationId'],
        );

        if ($decision->matchesBoundDevice) {
            $license->update([
                'last_validated_at' => now(),
            ]);

            return new ValidateLicenseResult(
                isValid: true,
                activationId: $activation->activation_id,
                licenseStatus: $license->status->value,
                graceUntil: $activation->grace_until?->toIso8601String(),
                entitlements: $this->presentEntitlements($license),
                reasonCode: null,
            );
        }

        $activation->update([
            'grace_until' => $decision->graceUntil,
            'last_reason_code' => $decision->reasonCode,
        ]);

        return new ValidateLicenseResult(
            isValid: $decision->allowed,
            activationId: $activation->activation_id,
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

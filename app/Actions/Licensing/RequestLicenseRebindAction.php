<?php

namespace App\Actions\Licensing;

use App\Domain\Licensing\LicenseReasonCode;
use App\Domain\Licensing\LicenseStatus;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Services\Licensing\AntiClonePolicyService;
use App\Services\Licensing\LicenseNotificationService;
use App\Support\Licensing\RebindRequestResult;
use Illuminate\Validation\ValidationException;

class RequestLicenseRebindAction
{
    public function __construct(
        private readonly AntiClonePolicyService $antiClonePolicy,
        private readonly LicenseNotificationService $notifications,
    ) {
    }

    /**
     * @param  array{licenseKey:string,activationToken:string,appId:string,appVersion:string,machineId:string,installationId:string}  $payload
     */
    public function execute(array $payload): RebindRequestResult
    {
        [$license, $activation] = $this->resolveLicenseActivation($payload);

        if ($license->status !== LicenseStatus::ACTIVE) {
            return new RebindRequestResult(
                requested: false,
                requiresManualReview: false,
                activationId: $activation->activation_id,
                licenseStatus: $license->status->value,
                graceUntil: null,
                reasonCode: LicenseReasonCode::forLicenseStatus($license->status),
            );
        }

        $decision = $this->antiClonePolicy->evaluate(
            $activation,
            $payload['machineId'],
            $payload['installationId'],
        );

        if ($decision->matchesBoundDevice) {
            return new RebindRequestResult(
                requested: false,
                requiresManualReview: false,
                activationId: $activation->activation_id,
                licenseStatus: $license->status->value,
                graceUntil: null,
                reasonCode: null,
            );
        }

        $activation->update([
            'grace_until' => $decision->graceUntil,
            'last_reason_code' => $decision->reasonCode,
        ]);

        $this->notifications->queueRebindRequested(
            activation: $activation->fresh(['license.app']),
            requestedMachineId: $payload['machineId'],
            requestedInstallationId: $payload['installationId'],
            graceUntil: $decision->graceUntil,
        );

        return new RebindRequestResult(
            requested: true,
            requiresManualReview: true,
            activationId: $activation->activation_id,
            licenseStatus: $license->status->value,
            graceUntil: $decision->graceUntil?->toIso8601String(),
            reasonCode: $decision->reasonCode,
        );
    }

    /**
     * @param  array{licenseKey:string,activationToken:string,appId:string,appVersion:string,machineId:string,installationId:string}  $payload
     * @return array{License, LicenseActivation}
     */
    private function resolveLicenseActivation(array $payload): array
    {
        $license = License::query()
            ->with('app')
            ->where('license_key', $payload['licenseKey'])
            ->whereHas('app', fn ($query) => $query->where('app_id', $payload['appId']))
            ->first();

        if ($license === null) {
            throw ValidationException::withMessages([
                'licenseKey' => 'The provided license could not be found for this application.',
            ]);
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

        return [$license, $activation];
    }
}

<?php

namespace App\Actions\Licensing;

use App\Domain\Licensing\LicenseReasonCode;
use App\Domain\Licensing\LicenseStatus;
use App\Models\LicenseActivation;
use App\Services\Licensing\AntiClonePolicyService;
use App\Services\Licensing\LicenseHeartbeatService;
use App\Services\Licensing\LicenseNotificationService;
use App\Support\Licensing\RecordHeartbeatResult;
use Illuminate\Validation\ValidationException;

class RecordHeartbeatAction
{
    public function __construct(
        private readonly AntiClonePolicyService $antiClonePolicy,
        private readonly LicenseHeartbeatService $heartbeatService,
        private readonly LicenseNotificationService $notifications,
    ) {
    }

    /**
     * @param  array{activationId:string,activationToken:string,appId:string,appVersion:string,machineId:string,installationId:string,ipAddress:?string}  $payload
     */
    public function execute(array $payload): RecordHeartbeatResult
    {
        $activation = LicenseActivation::query()
            ->with(['license.app'])
            ->where('activation_id', $payload['activationId'])
            ->first();

        if ($activation === null || ! $activation->matchesActivationToken($payload['activationToken'])) {
            throw ValidationException::withMessages([
                'activationToken' => 'The provided activation credentials are invalid.',
            ]);
        }

        if ($activation->license->app->app_id !== $payload['appId']) {
            throw ValidationException::withMessages([
                'appId' => 'The provided activation does not belong to this application.',
            ]);
        }

        $license = $activation->license;

        if ($license->status !== LicenseStatus::ACTIVE) {
            return new RecordHeartbeatResult(
                accepted: false,
                activationId: $activation->activation_id,
                licenseStatus: $license->status->value,
                activationStatus: $activation->status->value,
                graceUntil: $activation->grace_until?->toIso8601String(),
                reasonCode: LicenseReasonCode::forLicenseStatus($license->status),
            );
        }

        $decision = $this->antiClonePolicy->evaluate(
            $activation,
            $payload['machineId'],
            $payload['installationId'],
        );
        $previousReasonCode = $activation->last_reason_code;

        $activation->update([
            'grace_until' => $decision->graceUntil,
            'last_reason_code' => $decision->reasonCode,
        ]);

        $this->heartbeatService->recordHeartbeat(
            activation: $activation->fresh(),
            appVersion: $payload['appVersion'],
            ipAddress: $payload['ipAddress'],
            reasonCode: $decision->reasonCode,
        );

        $license->update([
            'last_validated_at' => now(),
        ]);

        if ($decision->reasonCode === LicenseReasonCode::DEVICE_MISMATCH
            && $previousReasonCode !== LicenseReasonCode::DEVICE_MISMATCH) {
            $this->notifications->queueDeviceMismatchAlert(
                activation: $activation->fresh(['license.app']),
                machineId: $payload['machineId'],
                installationId: $payload['installationId'],
                reasonCode: $decision->reasonCode,
                graceUntil: $decision->graceUntil,
            );
        }

        return new RecordHeartbeatResult(
            accepted: $decision->allowed,
            activationId: $activation->activation_id,
            licenseStatus: $license->status->value,
            activationStatus: $activation->fresh()->status->value,
            graceUntil: $decision->graceUntil?->toIso8601String(),
            reasonCode: $decision->reasonCode,
        );
    }
}

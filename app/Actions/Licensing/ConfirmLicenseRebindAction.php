<?php

namespace App\Actions\Licensing;

use App\Domain\Licensing\LicenseReasonCode;
use App\Domain\Licensing\LicenseStatus;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Support\Licensing\RebindConfirmResult;
use Illuminate\Validation\ValidationException;

class ConfirmLicenseRebindAction
{
    /**
     * @param  array{licenseKey:string,activationToken:string,appId:string,appVersion:string,machineId:string,installationId:string}  $payload
     */
    public function execute(array $payload): RebindConfirmResult
    {
        [$license, $activation] = $this->resolveLicenseActivation($payload);

        if ($license->status !== LicenseStatus::ACTIVE) {
            return new RebindConfirmResult(
                confirmed: false,
                activationId: $activation->activation_id,
                licenseStatus: $license->status->value,
                graceUntil: null,
                reasonCode: LicenseReasonCode::forLicenseStatus($license->status),
            );
        }

        if (! $activation->matchesDevice($payload['machineId'], $payload['installationId'])) {
            return new RebindConfirmResult(
                confirmed: false,
                activationId: $activation->activation_id,
                licenseStatus: $license->status->value,
                graceUntil: $activation->grace_until?->toIso8601String(),
                reasonCode: LicenseReasonCode::REBIND_PENDING_MANUAL_CONFIRMATION,
            );
        }

        $activation->update([
            'grace_until' => null,
            'last_reason_code' => null,
            'last_seen_at' => now(),
        ]);

        $license->update([
            'last_validated_at' => now(),
        ]);

        return new RebindConfirmResult(
            confirmed: true,
            activationId: $activation->activation_id,
            licenseStatus: $license->status->value,
            graceUntil: null,
            reasonCode: null,
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

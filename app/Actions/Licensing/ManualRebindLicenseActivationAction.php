<?php

namespace App\Actions\Licensing;

use App\Domain\Licensing\LicenseActivationStatus;
use App\Models\LicenseActivation;
use App\Services\Licensing\LicenseNotificationService;

class ManualRebindLicenseActivationAction
{
    public function __construct(
        private readonly LicenseNotificationService $notifications,
    ) {
    }

    /**
     * @param  array{machine_id:string,installation_id:string,device_label:?string}  $attributes
     */
    public function execute(LicenseActivation $activation, array $attributes): LicenseActivation
    {
        $timestamp = now();

        $activation->update([
            'machine_id' => $attributes['machine_id'],
            'installation_id' => $attributes['installation_id'],
            'device_label' => $attributes['device_label'] ?? null,
            'status' => LicenseActivationStatus::ACTIVE,
            'first_seen_at' => $timestamp,
            'last_seen_at' => $timestamp,
            'grace_until' => null,
            'last_reason_code' => null,
        ]);

        $reboundActivation = $activation->fresh(['license.app']);

        $this->notifications->queueRebindConfirmed($reboundActivation);

        return $reboundActivation;
    }
}

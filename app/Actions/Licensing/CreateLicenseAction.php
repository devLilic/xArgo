<?php

namespace App\Actions\Licensing;

use App\Domain\Licensing\LicenseStatus;
use App\Models\License;
use App\Models\LicensePlan;
use App\Services\Licensing\LicenseIdentifierGenerator;
use Carbon\CarbonInterface;

class CreateLicenseAction
{
    public function __construct(
        private readonly LicenseIdentifierGenerator $identifierGenerator,
    ) {
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(LicensePlan $plan, array $attributes = []): License
    {
        return License::query()->create([
            'app_id' => $plan->app_id,
            'plan_id' => $plan->id,
            'public_key' => $this->identifierGenerator->generatePublicKey(),
            'license_key' => $this->identifierGenerator->generateLicenseKey(),
            'customer_name' => $attributes['customer_name'] ?? null,
            'customer_email' => $attributes['customer_email'] ?? null,
            'status' => $attributes['status'] ?? LicenseStatus::ACTIVE,
            'max_devices' => $attributes['max_devices'] ?? $plan->default_max_devices,
            'expires_at' => $attributes['expires_at'] ?? null,
            'grace_hours' => $attributes['grace_hours'] ?? 24,
            'notes' => $attributes['notes'] ?? null,
            'last_validated_at' => $attributes['last_validated_at'] ?? null,
        ]);
    }
}

<?php

namespace App\Actions\Licensing;

use App\Models\License;

class UpdateLicenseAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(License $license, array $attributes): License
    {
        $license->update([
            'app_id' => $attributes['app_id'],
            'plan_id' => $attributes['plan_id'],
            'customer_name' => $attributes['customer_name'] ?? null,
            'customer_email' => $attributes['customer_email'] ?? null,
            'max_devices' => $attributes['max_devices'],
            'expires_at' => $attributes['expires_at'] ?? null,
            'grace_hours' => $attributes['grace_hours'],
            'notes' => $attributes['notes'] ?? null,
        ]);

        return $license->fresh();
    }
}

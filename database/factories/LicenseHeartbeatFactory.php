<?php

namespace Database\Factories;

use App\Models\LicenseActivation;
use App\Models\LicenseHeartbeat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LicenseHeartbeat>
 */
class LicenseHeartbeatFactory extends Factory
{
    protected $model = LicenseHeartbeat::class;

    public function definition(): array
    {
        return [
            'license_activation_id' => LicenseActivation::factory(),
            'app_version' => fake()->semver(),
            'received_at' => now(),
            'ip_address' => fake()->optional()->ipv4(),
            'reason_code' => fake()->optional()->randomElement([
                'ok',
                'device_mismatch',
                'stale_device',
            ]),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Domain\Licensing\LicenseActivationStatus;
use App\Models\License;
use App\Models\LicenseActivation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LicenseActivation>
 */
class LicenseActivationFactory extends Factory
{
    protected $model = LicenseActivation::class;

    public function definition(): array
    {
        return [
            'license_id' => License::factory(),
            'activation_id' => (string) Str::uuid(),
            'machine_id' => 'machine-'.fake()->unique()->bothify('####-####'),
            'installation_id' => (string) Str::uuid(),
            'activation_token_hash' => hash('sha256', Str::random(64)),
            'device_label' => fake()->optional()->words(2, true),
            'status' => LicenseActivationStatus::ACTIVE,
            'first_seen_at' => now()->subDay(),
            'last_seen_at' => now(),
            'grace_until' => null,
            'last_reason_code' => null,
        ];
    }
}

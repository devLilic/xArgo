<?php

namespace Database\Factories;

use App\Models\License;
use App\Models\LicenseEntitlement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LicenseEntitlement>
 */
class LicenseEntitlementFactory extends Factory
{
    protected $model = LicenseEntitlement::class;

    public function definition(): array
    {
        return [
            'license_id' => License::factory(),
            'feature_code' => fake()->unique()->slug(2, '_'),
            'enabled' => true,
        ];
    }
}

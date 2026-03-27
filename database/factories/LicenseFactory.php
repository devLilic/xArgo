<?php

namespace Database\Factories;

use App\Domain\Licensing\LicenseStatus;
use App\Models\App;
use App\Models\License;
use App\Models\LicensePlan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<License>
 */
class LicenseFactory extends Factory
{
    protected $model = License::class;

    public function configure(): static
    {
        return $this->afterMaking(function (License $license): void {
            if ($license->plan_id !== null) {
                $plan = LicensePlan::query()->find($license->plan_id);

                if ($plan !== null) {
                    $license->app_id ??= $plan->app_id;
                    $license->max_devices = $license->max_devices ?: $plan->default_max_devices;
                }
            }

            if ($license->plan_id !== null && $license->app_id !== null) {
                return;
            }

            $plan = LicensePlan::factory()->create();

            $license->plan_id = $plan->id;
            $license->app_id = $plan->app_id;
            $license->max_devices = $plan->default_max_devices;
        });
    }

    public function definition(): array
    {
        return [
            'app_id' => null,
            'plan_id' => null,
            'public_key' => (string) Str::uuid(),
            'license_key' => strtoupper(fake()->bothify('XARGO-####-####-####')),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->unique()->safeEmail(),
            'status' => LicenseStatus::ACTIVE,
            'max_devices' => 1,
            'expires_at' => null,
            'grace_hours' => 24,
            'notes' => null,
            'last_validated_at' => null,
        ];
    }
}

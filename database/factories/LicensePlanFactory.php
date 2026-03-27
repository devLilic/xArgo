<?php

namespace Database\Factories;

use App\Domain\Licensing\LicenseDurationType;
use App\Models\App;
use App\Models\LicensePlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LicensePlan>
 */
class LicensePlanFactory extends Factory
{
    protected $model = LicensePlan::class;

    public function definition(): array
    {
        $code = fake()->unique()->bothify('PLAN-###');

        return [
            'app_id' => App::factory(),
            'name' => fake()->words(2, true),
            'code' => $code,
            'duration_type' => LicenseDurationType::PERMANENT,
            'duration_days' => null,
            'default_max_devices' => 1,
            'is_active' => true,
        ];
    }

    public function subscription(int $days = 30): static
    {
        return $this->state(fn (): array => [
            'duration_type' => LicenseDurationType::SUBSCRIPTION,
            'duration_days' => $days,
        ]);
    }

    public function trial(int $days = 14): static
    {
        return $this->state(fn (): array => [
            'duration_type' => LicenseDurationType::TRIAL,
            'duration_days' => $days,
        ]);
    }
}

<?php

namespace Tests\Feature\Apps;

use App\Domain\Licensing\LicenseDurationType;
use App\Models\App;
use App\Models\LicensePlan;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LicensePlanModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_license_plans_table_exposes_the_required_schema(): void
    {
        $this->assertTrue(Schema::hasTable('license_plans'));
        $this->assertTrue(Schema::hasColumns('license_plans', [
            'app_id',
            'name',
            'code',
            'duration_type',
            'duration_days',
            'default_max_devices',
            'is_active',
            'deleted_at',
        ]));
    }

    public function test_license_plan_belongs_to_an_app(): void
    {
        $app = App::factory()->create();

        $plan = LicensePlan::factory()->create([
            'app_id' => $app->id,
            'code' => 'PRO-MONTHLY',
            'duration_type' => LicenseDurationType::SUBSCRIPTION,
            'duration_days' => 30,
        ]);

        $this->assertTrue($plan->app->is($app));
        $this->assertCount(1, $app->fresh()->licensePlans);
        $this->assertSame(LicenseDurationType::SUBSCRIPTION, $plan->duration_type);
    }

    public function test_plan_code_is_unique_per_app(): void
    {
        $app = App::factory()->create();

        LicensePlan::factory()->create([
            'app_id' => $app->id,
            'code' => 'TRIAL-14',
        ]);

        $this->expectException(QueryException::class);

        LicensePlan::factory()->create([
            'app_id' => $app->id,
            'code' => 'TRIAL-14',
        ]);
    }

    public function test_same_plan_code_can_be_used_by_different_apps(): void
    {
        LicensePlan::factory()->create([
            'app_id' => App::factory()->create()->id,
            'code' => 'STANDARD',
        ]);

        $plan = LicensePlan::factory()->create([
            'app_id' => App::factory()->create()->id,
            'code' => 'STANDARD',
        ]);

        $this->assertSame('STANDARD', $plan->code);
    }
}

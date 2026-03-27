<?php

namespace Tests\Feature\Apps;

use App\Domain\Licensing\LicenseStatus;
use App\Models\App;
use App\Models\License;
use App\Models\LicensePlan;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LicenseModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_licenses_table_exposes_the_required_schema(): void
    {
        $this->assertTrue(Schema::hasTable('licenses'));
        $this->assertTrue(Schema::hasColumns('licenses', [
            'app_id',
            'plan_id',
            'public_key',
            'license_key',
            'customer_name',
            'customer_email',
            'status',
            'max_devices',
            'expires_at',
            'grace_hours',
            'notes',
            'last_validated_at',
            'deleted_at',
        ]));
    }

    public function test_license_belongs_to_app_and_plan(): void
    {
        $app = App::factory()->create();
        $plan = LicensePlan::factory()->create([
            'app_id' => $app->id,
        ]);

        $license = License::factory()->create([
            'app_id' => $app->id,
            'plan_id' => $plan->id,
            'public_key' => 'public-license-key',
            'license_key' => 'XARGO-1111-2222-3333',
            'status' => LicenseStatus::ACTIVE,
        ]);

        $this->assertTrue($license->app->is($app));
        $this->assertTrue($license->plan->is($plan));
        $this->assertCount(1, $app->fresh()->licenses);
        $this->assertCount(1, $plan->fresh()->licenses);
        $this->assertSame(LicenseStatus::ACTIVE, $license->status);
    }

    public function test_public_key_and_license_key_must_be_unique(): void
    {
        License::factory()->create([
            'public_key' => 'shared-public-key',
            'license_key' => 'XARGO-AAAA-BBBB-CCCC',
        ]);

        $this->expectException(QueryException::class);

        License::factory()->create([
            'public_key' => 'shared-public-key',
            'license_key' => 'XARGO-ZZZZ-YYYY-XXXX',
        ]);
    }

    public function test_license_key_must_be_unique(): void
    {
        License::factory()->create([
            'public_key' => 'public-key-one',
            'license_key' => 'XARGO-SAME-KEY-0001',
        ]);

        $this->expectException(QueryException::class);

        License::factory()->create([
            'public_key' => 'public-key-two',
            'license_key' => 'XARGO-SAME-KEY-0001',
        ]);
    }
}

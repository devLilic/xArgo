<?php

namespace Tests\Feature\Apps;

use App\Models\License;
use App\Models\LicenseEntitlement;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LicenseEntitlementModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_license_entitlements_table_exposes_the_required_schema(): void
    {
        $this->assertTrue(Schema::hasTable('license_entitlements'));
        $this->assertTrue(Schema::hasColumns('license_entitlements', [
            'license_id',
            'feature_code',
            'enabled',
        ]));
    }

    public function test_entitlement_belongs_to_a_license(): void
    {
        $license = License::factory()->create();

        $entitlement = LicenseEntitlement::factory()->create([
            'license_id' => $license->id,
            'feature_code' => 'offline_mode',
            'enabled' => true,
        ]);

        $this->assertTrue($entitlement->license->is($license));
        $this->assertCount(1, $license->fresh()->entitlements);
        $this->assertTrue($entitlement->enabled);
    }

    public function test_feature_code_must_be_unique_per_license(): void
    {
        $license = License::factory()->create();

        LicenseEntitlement::factory()->create([
            'license_id' => $license->id,
            'feature_code' => 'export_pdf',
        ]);

        $this->expectException(QueryException::class);

        LicenseEntitlement::factory()->create([
            'license_id' => $license->id,
            'feature_code' => 'export_pdf',
        ]);
    }

    public function test_multiple_entitlements_persist_with_their_enabled_state(): void
    {
        $license = License::factory()->create();

        LicenseEntitlement::factory()->create([
            'license_id' => $license->id,
            'feature_code' => 'offline_mode',
            'enabled' => true,
        ]);

        LicenseEntitlement::factory()->create([
            'license_id' => $license->id,
            'feature_code' => 'priority_support',
            'enabled' => false,
        ]);

        $entitlements = $license->fresh()->entitlements->keyBy('feature_code');

        $this->assertCount(2, $entitlements);
        $this->assertTrue($entitlements['offline_mode']->enabled);
        $this->assertFalse($entitlements['priority_support']->enabled);
    }
}

<?php

namespace Tests\Feature\Apps;

use App\Domain\Licensing\LicenseActivationStatus;
use App\Models\License;
use App\Models\LicenseActivation;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LicenseActivationModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_license_activations_table_exposes_the_required_schema(): void
    {
        $this->assertTrue(Schema::hasTable('license_activations'));
        $this->assertTrue(Schema::hasColumns('license_activations', [
            'license_id',
            'activation_id',
            'machine_id',
            'installation_id',
            'activation_token_hash',
            'device_label',
            'status',
            'first_seen_at',
            'last_seen_at',
            'grace_until',
            'last_reason_code',
            'deleted_at',
        ]));
    }

    public function test_activation_belongs_to_a_license(): void
    {
        $license = License::factory()->create();

        $activation = LicenseActivation::factory()->create([
            'license_id' => $license->id,
            'status' => LicenseActivationStatus::ACTIVE,
            'last_reason_code' => 'device_mismatch',
        ]);

        $this->assertTrue($activation->license->is($license));
        $this->assertCount(1, $license->fresh()->activations);
        $this->assertSame(LicenseActivationStatus::ACTIVE, $activation->status);
        $this->assertSame('device_mismatch', $activation->last_reason_code);
    }

    public function test_activation_id_must_be_unique(): void
    {
        LicenseActivation::factory()->create([
            'activation_id' => '9ecdf7bd-61ca-4f8d-a8f8-1e8ef2760abc',
        ]);

        $this->expectException(QueryException::class);

        LicenseActivation::factory()->create([
            'activation_id' => '9ecdf7bd-61ca-4f8d-a8f8-1e8ef2760abc',
        ]);
    }
}

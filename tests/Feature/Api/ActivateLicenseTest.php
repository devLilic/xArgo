<?php

namespace Tests\Feature\Api;

use App\Domain\Licensing\LicenseStatus;
use App\Models\App;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\LicenseEntitlement;
use App\Models\LicensePlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivateLicenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_activates_a_valid_license_and_returns_the_expected_contract(): void
    {
        $app = App::factory()->create(['app_id' => 'xargo.desktop']);
        $plan = LicensePlan::factory()->create(['app_id' => $app->id]);
        $license = License::factory()->create([
            'app_id' => $app->id,
            'plan_id' => $plan->id,
            'license_key' => 'XARGO-ACT-0001',
            'status' => LicenseStatus::ACTIVE,
        ]);
        LicenseEntitlement::factory()->create([
            'license_id' => $license->id,
            'feature_code' => 'pro_export',
            'enabled' => true,
        ]);

        $response = $this->postJson('/api/v1/licenses/activate', [
            'licenseKey' => 'XARGO-ACT-0001',
            'appId' => 'xargo.desktop',
            'appVersion' => '2.0.0',
            'machineId' => 'machine-001',
            'installationId' => 'installation-001',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('error', null)
            ->assertJsonPath('data.licenseStatus', 'active')
            ->assertJsonPath('data.graceUntil', null)
            ->assertJsonPath('data.reasonCode', null)
            ->assertJsonPath('data.entitlements.0.featureCode', 'pro_export')
            ->assertJsonPath('data.entitlements.0.enabled', true)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'activationId',
                    'activationToken',
                    'licenseStatus',
                    'graceUntil',
                    'entitlements',
                    'reasonCode',
                ],
                'error',
            ]);

        $this->assertDatabaseHas('license_activations', [
            'license_id' => $license->id,
            'machine_id' => 'machine-001',
            'installation_id' => 'installation-001',
        ]);
    }

    public function test_it_reissues_activation_token_for_an_existing_bound_device(): void
    {
        $app = App::factory()->create(['app_id' => 'xargo.desktop']);
        $plan = LicensePlan::factory()->create(['app_id' => $app->id]);
        $license = License::factory()->create([
            'app_id' => $app->id,
            'plan_id' => $plan->id,
            'license_key' => 'XARGO-ACT-REUSE',
        ]);
        $activation = LicenseActivation::factory()->create([
            'license_id' => $license->id,
            'machine_id' => 'machine-001',
            'installation_id' => 'installation-001',
            'grace_until' => null,
            'last_reason_code' => 'device_mismatch',
        ]);

        $response = $this->postJson('/api/v1/licenses/activate', [
            'licenseKey' => 'XARGO-ACT-REUSE',
            'appId' => 'xargo.desktop',
            'appVersion' => '2.0.0',
            'machineId' => 'machine-001',
            'installationId' => 'installation-001',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.activationId', $activation->activation_id)
            ->assertJsonPath('data.licenseStatus', 'active')
            ->assertJsonPath('data.reasonCode', null);

        $this->assertNotNull($response->json('data.activationToken'));
        $this->assertNull($activation->fresh()->last_reason_code);
    }

    public function test_it_returns_device_mismatch_context_when_activation_attempt_comes_from_a_second_device(): void
    {
        $app = App::factory()->create(['app_id' => 'xargo.desktop']);
        $plan = LicensePlan::factory()->create([
            'app_id' => $app->id,
            'default_max_devices' => 1,
        ]);
        $license = License::factory()->create([
            'app_id' => $app->id,
            'plan_id' => $plan->id,
            'license_key' => 'XARGO-ACT-MISMATCH',
            'max_devices' => 1,
        ]);
        $activation = LicenseActivation::factory()->create([
            'license_id' => $license->id,
            'machine_id' => 'machine-primary',
            'installation_id' => 'installation-primary',
            'grace_until' => null,
        ]);

        $response = $this->postJson('/api/v1/licenses/activate', [
            'licenseKey' => 'XARGO-ACT-MISMATCH',
            'appId' => 'xargo.desktop',
            'appVersion' => '2.0.0',
            'machineId' => 'machine-clone',
            'installationId' => 'installation-clone',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.activationId', $activation->activation_id)
            ->assertJsonPath('data.activationToken', null)
            ->assertJsonPath('data.licenseStatus', 'active')
            ->assertJsonPath('data.reasonCode', 'device_mismatch');

        $this->assertNotNull($response->json('data.graceUntil'));
        $this->assertSame('device_mismatch', $activation->fresh()->last_reason_code);
        $this->assertSame('machine-primary', $activation->fresh()->machine_id);
    }

    public function test_it_rejects_incomplete_activation_payloads(): void
    {
        $response = $this->postJson('/api/v1/licenses/activate', []);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('data', null)
            ->assertJsonPath('error.code', 'validation_error')
            ->assertJsonPath('error.reasonCode', 'validation_failed')
            ->assertJsonPath('error.details.licenseKey.0', 'The license key field is required.')
            ->assertJsonPath('error.details.appId.0', 'The app id field is required.')
            ->assertJsonPath('error.details.appVersion.0', 'The app version field is required.')
            ->assertJsonPath('error.details.machineId.0', 'The machine id field is required.')
            ->assertJsonPath('error.details.installationId.0', 'The installation id field is required.');
    }

    public function test_it_rejects_an_invalid_license_key_for_activation(): void
    {
        App::factory()->create(['app_id' => 'xargo.desktop']);

        $response = $this->postJson('/api/v1/licenses/activate', [
            'licenseKey' => 'XARGO-INVALID-9999',
            'appId' => 'xargo.desktop',
            'appVersion' => '2.0.0',
            'machineId' => 'machine-001',
            'installationId' => 'installation-001',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('data', null)
            ->assertJsonPath('error.code', 'validation_error')
            ->assertJsonPath('error.reasonCode', 'validation_failed')
            ->assertJsonPath(
                'error.details.licenseKey.0',
                'The provided license could not be activated for this application.'
            );
    }

    public function test_it_returns_expired_license_state_for_activation_attempts(): void
    {
        $app = App::factory()->create(['app_id' => 'xargo.desktop']);
        $plan = LicensePlan::factory()->create(['app_id' => $app->id]);
        $license = License::factory()->create([
            'app_id' => $app->id,
            'plan_id' => $plan->id,
            'license_key' => 'XARGO-ACT-EXPIRED',
            'status' => LicenseStatus::EXPIRED,
        ]);

        $response = $this->postJson('/api/v1/licenses/activate', [
            'licenseKey' => 'XARGO-ACT-EXPIRED',
            'appId' => 'xargo.desktop',
            'appVersion' => '2.0.0',
            'machineId' => 'machine-001',
            'installationId' => 'installation-001',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.activationId', null)
            ->assertJsonPath('data.activationToken', null)
            ->assertJsonPath('data.licenseStatus', 'expired')
            ->assertJsonPath('data.reasonCode', 'license_expired');

        $this->assertDatabaseMissing('license_activations', [
            'license_id' => $license->id,
        ]);
    }
}

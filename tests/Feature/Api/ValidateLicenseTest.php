<?php

namespace Tests\Feature\Api;

use App\Domain\Licensing\LicenseActivationStatus;
use App\Models\App;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\LicenseEntitlement;
use App\Models\LicensePlan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidateLicenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_validates_the_bound_device_with_a_matching_activation_token(): void
    {
        $app = App::factory()->create(['app_id' => 'xargo.desktop']);
        $plan = LicensePlan::factory()->create(['app_id' => $app->id]);
        $license = License::factory()->create([
            'app_id' => $app->id,
            'plan_id' => $plan->id,
            'license_key' => 'XARGO-VAL-0001',
        ]);
        LicenseEntitlement::factory()->create([
            'license_id' => $license->id,
            'feature_code' => 'cloud_sync',
            'enabled' => true,
        ]);

        $plainToken = str_repeat('a', 80);
        $activation = LicenseActivation::factory()->create([
            'license_id' => $license->id,
            'machine_id' => 'machine-001',
            'installation_id' => 'installation-001',
            'activation_token_hash' => hash('sha256', $plainToken),
            'status' => LicenseActivationStatus::ACTIVE,
        ]);

        $response = $this->postJson('/api/v1/licenses/validate', [
            'licenseKey' => 'XARGO-VAL-0001',
            'activationToken' => $plainToken,
            'appId' => 'xargo.desktop',
            'appVersion' => '2.1.0',
            'machineId' => 'machine-001',
            'installationId' => 'installation-001',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.isValid', true)
            ->assertJsonPath('data.activationId', $activation->activation_id)
            ->assertJsonPath('data.licenseStatus', 'active')
            ->assertJsonPath('data.reasonCode', null)
            ->assertJsonPath('data.entitlements.0.featureCode', 'cloud_sync');
    }

    public function test_it_returns_grace_context_for_a_device_mismatch_without_rebinding_the_original_activation(): void
    {
        Carbon::setTestNow('2026-03-27 18:00:00');

        try {
            $app = App::factory()->create(['app_id' => 'xargo.desktop']);
            $plan = LicensePlan::factory()->create(['app_id' => $app->id]);
            $license = License::factory()->create([
                'app_id' => $app->id,
                'plan_id' => $plan->id,
                'license_key' => 'XARGO-VAL-MISMATCH',
                'max_devices' => 1,
            ]);

            $plainToken = str_repeat('b', 80);
            $activation = LicenseActivation::factory()->create([
                'license_id' => $license->id,
                'machine_id' => 'machine-primary',
                'installation_id' => 'installation-primary',
                'activation_token_hash' => hash('sha256', $plainToken),
                'status' => LicenseActivationStatus::ACTIVE,
                'grace_until' => null,
            ]);

            $response = $this->postJson('/api/v1/licenses/validate', [
                'licenseKey' => 'XARGO-VAL-MISMATCH',
                'activationToken' => $plainToken,
                'appId' => 'xargo.desktop',
                'appVersion' => '2.1.0',
                'machineId' => 'machine-clone',
                'installationId' => 'installation-clone',
            ]);

            $response
                ->assertOk()
                ->assertJsonPath('status', 'success')
                ->assertJsonPath('data.isValid', true)
                ->assertJsonPath('data.activationId', $activation->activation_id)
                ->assertJsonPath('data.licenseStatus', 'active')
                ->assertJsonPath('data.reasonCode', 'device_mismatch');

            $this->assertNotNull($response->json('data.graceUntil'));
            $this->assertSame('machine-primary', $activation->fresh()->machine_id);
            $this->assertSame('installation-primary', $activation->fresh()->installation_id);
            $this->assertSame(LicenseActivationStatus::ACTIVE, $activation->fresh()->status);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_it_blocks_validation_after_device_mismatch_grace_has_expired(): void
    {
        $app = App::factory()->create(['app_id' => 'xargo.desktop']);
        $plan = LicensePlan::factory()->create(['app_id' => $app->id]);
        $license = License::factory()->create([
            'app_id' => $app->id,
            'plan_id' => $plan->id,
            'license_key' => 'XARGO-VAL-BLOCK',
            'max_devices' => 1,
        ]);

        $plainToken = str_repeat('c', 80);
        $activation = LicenseActivation::factory()->create([
            'license_id' => $license->id,
            'machine_id' => 'machine-primary',
            'installation_id' => 'installation-primary',
            'activation_token_hash' => hash('sha256', $plainToken),
            'status' => LicenseActivationStatus::ACTIVE,
            'grace_until' => Carbon::parse('2026-03-27 17:59:00'),
        ]);

        Carbon::setTestNow('2026-03-27 18:10:00');

        try {
            $response = $this->postJson('/api/v1/licenses/validate', [
                'licenseKey' => 'XARGO-VAL-BLOCK',
                'activationToken' => $plainToken,
                'appId' => 'xargo.desktop',
                'appVersion' => '2.1.0',
                'machineId' => 'machine-clone',
                'installationId' => 'installation-clone',
            ]);
        } finally {
            Carbon::setTestNow();
        }

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.isValid', false)
            ->assertJsonPath('data.activationId', $activation->activation_id)
            ->assertJsonPath('data.licenseStatus', 'active')
            ->assertJsonPath('data.reasonCode', 'device_mismatch')
            ->assertJsonPath('data.graceUntil', '2026-03-27T17:59:00+00:00');

        $this->assertSame(LicenseActivationStatus::ACTIVE, $activation->fresh()->status);
        $this->assertSame('machine-primary', $activation->fresh()->machine_id);
    }

    public function test_it_rejects_invalid_activation_tokens(): void
    {
        $app = App::factory()->create(['app_id' => 'xargo.desktop']);
        $plan = LicensePlan::factory()->create(['app_id' => $app->id]);
        $license = License::factory()->create([
            'app_id' => $app->id,
            'plan_id' => $plan->id,
            'license_key' => 'XARGO-VAL-TOKEN',
        ]);

        LicenseActivation::factory()->create([
            'license_id' => $license->id,
        ]);

        $response = $this->postJson('/api/v1/licenses/validate', [
            'licenseKey' => 'XARGO-VAL-TOKEN',
            'activationToken' => 'invalid-token',
            'appId' => 'xargo.desktop',
            'appVersion' => '2.1.0',
            'machineId' => 'machine-001',
            'installationId' => 'installation-001',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'validation_error')
            ->assertJsonPath('error.reasonCode', 'validation_failed')
            ->assertJsonPath('error.details.activationToken.0', 'The provided activation token is invalid for this license.');
    }

    public function test_it_rejects_incomplete_validation_payloads(): void
    {
        $response = $this->postJson('/api/v1/licenses/validate', []);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'validation_error')
            ->assertJsonPath('error.reasonCode', 'validation_failed')
            ->assertJsonPath('error.details.licenseKey.0', 'The license key field is required.')
            ->assertJsonPath('error.details.activationToken.0', 'The activation token field is required.')
            ->assertJsonPath('error.details.appId.0', 'The app id field is required.')
            ->assertJsonPath('error.details.appVersion.0', 'The app version field is required.')
            ->assertJsonPath('error.details.machineId.0', 'The machine id field is required.')
            ->assertJsonPath('error.details.installationId.0', 'The installation id field is required.');
    }

    public function test_it_returns_revoked_license_state_during_validation(): void
    {
        $app = App::factory()->create(['app_id' => 'xargo.desktop']);
        $plan = LicensePlan::factory()->create(['app_id' => $app->id]);
        $license = License::factory()->create([
            'app_id' => $app->id,
            'plan_id' => $plan->id,
            'license_key' => 'XARGO-VAL-REVOKED',
            'status' => \App\Domain\Licensing\LicenseStatus::REVOKED,
        ]);

        $response = $this->postJson('/api/v1/licenses/validate', [
            'licenseKey' => 'XARGO-VAL-REVOKED',
            'activationToken' => str_repeat('z', 80),
            'appId' => 'xargo.desktop',
            'appVersion' => '2.1.0',
            'machineId' => 'machine-001',
            'installationId' => 'installation-001',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.isValid', false)
            ->assertJsonPath('data.activationId', null)
            ->assertJsonPath('data.licenseStatus', 'revoked')
            ->assertJsonPath('data.reasonCode', 'license_revoked');
    }
}

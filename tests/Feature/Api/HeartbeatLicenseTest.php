<?php

namespace Tests\Feature\Api;

use App\Domain\Licensing\LicenseStatus;
use App\Domain\Licensing\LicenseActivationStatus;
use App\Models\App;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\LicensePlan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeartbeatLicenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_a_heartbeat_and_updates_activation_state_for_the_bound_device(): void
    {
        Carbon::setTestNow('2026-03-27 19:00:00');

        try {
            $app = App::factory()->create(['app_id' => 'xargo.desktop']);
            $plan = LicensePlan::factory()->create(['app_id' => $app->id]);
            $license = License::factory()->create([
                'app_id' => $app->id,
                'plan_id' => $plan->id,
            ]);
            $plainToken = str_repeat('d', 80);
            $activation = LicenseActivation::factory()->create([
                'license_id' => $license->id,
                'activation_id' => 'activation-heartbeat-001',
                'machine_id' => 'machine-001',
                'installation_id' => 'installation-001',
                'activation_token_hash' => hash('sha256', $plainToken),
                'status' => LicenseActivationStatus::ACTIVE,
                'last_seen_at' => Carbon::parse('2026-03-27 18:00:00'),
            ]);

            $response = $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.50'])
                ->postJson('/api/v1/licenses/heartbeat', [
                    'activationId' => 'activation-heartbeat-001',
                    'activationToken' => $plainToken,
                    'appId' => 'xargo.desktop',
                    'appVersion' => '2.2.0',
                    'machineId' => 'machine-001',
                    'installationId' => 'installation-001',
                ]);

            $response
                ->assertOk()
                ->assertJsonPath('status', 'success')
                ->assertJsonPath('data.accepted', true)
                ->assertJsonPath('data.activationId', 'activation-heartbeat-001')
                ->assertJsonPath('data.licenseStatus', 'active')
                ->assertJsonPath('data.activationStatus', 'active')
                ->assertJsonPath('data.graceUntil', null)
                ->assertJsonPath('data.reasonCode', null);

            $this->assertDatabaseHas('license_heartbeats', [
                'license_activation_id' => $activation->id,
                'app_version' => '2.2.0',
                'ip_address' => '203.0.113.50',
                'reason_code' => null,
            ]);
            $this->assertTrue(now()->equalTo($activation->fresh()->last_seen_at));
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_it_returns_grace_context_for_a_mismatched_heartbeat_without_rebinding_the_activation(): void
    {
        Carbon::setTestNow('2026-03-27 20:00:00');

        try {
            $app = App::factory()->create(['app_id' => 'xargo.desktop']);
            $plan = LicensePlan::factory()->create(['app_id' => $app->id]);
            $license = License::factory()->create([
                'app_id' => $app->id,
                'plan_id' => $plan->id,
            ]);
            $plainToken = str_repeat('e', 80);
            $activation = LicenseActivation::factory()->create([
                'license_id' => $license->id,
                'activation_id' => 'activation-heartbeat-002',
                'machine_id' => 'machine-primary',
                'installation_id' => 'installation-primary',
                'activation_token_hash' => hash('sha256', $plainToken),
                'status' => LicenseActivationStatus::ACTIVE,
                'grace_until' => null,
            ]);

            $response = $this->postJson('/api/v1/licenses/heartbeat', [
                'activationId' => 'activation-heartbeat-002',
                'activationToken' => $plainToken,
                'appId' => 'xargo.desktop',
                'appVersion' => '2.2.0',
                'machineId' => 'machine-clone',
                'installationId' => 'installation-clone',
            ]);

            $response
                ->assertOk()
                ->assertJsonPath('status', 'success')
                ->assertJsonPath('data.accepted', true)
                ->assertJsonPath('data.activationId', 'activation-heartbeat-002')
                ->assertJsonPath('data.reasonCode', 'device_mismatch');

            $this->assertNotNull($response->json('data.graceUntil'));
            $this->assertSame('machine-primary', $activation->fresh()->machine_id);
            $this->assertSame('installation-primary', $activation->fresh()->installation_id);
            $this->assertDatabaseHas('license_heartbeats', [
                'license_activation_id' => $activation->id,
                'reason_code' => 'device_mismatch',
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_it_blocks_heartbeat_after_grace_has_expired(): void
    {
        Carbon::setTestNow('2026-03-27 21:00:00');

        try {
            $app = App::factory()->create(['app_id' => 'xargo.desktop']);
            $plan = LicensePlan::factory()->create(['app_id' => $app->id]);
            $license = License::factory()->create([
                'app_id' => $app->id,
                'plan_id' => $plan->id,
            ]);
            $plainToken = str_repeat('f', 80);
            $activation = LicenseActivation::factory()->create([
                'license_id' => $license->id,
                'activation_id' => 'activation-heartbeat-003',
                'machine_id' => 'machine-primary',
                'installation_id' => 'installation-primary',
                'activation_token_hash' => hash('sha256', $plainToken),
                'status' => LicenseActivationStatus::ACTIVE,
                'grace_until' => Carbon::parse('2026-03-27 20:30:00'),
            ]);

            $response = $this->postJson('/api/v1/licenses/heartbeat', [
                'activationId' => 'activation-heartbeat-003',
                'activationToken' => $plainToken,
                'appId' => 'xargo.desktop',
                'appVersion' => '2.2.0',
                'machineId' => 'machine-clone',
                'installationId' => 'installation-clone',
            ]);

            $response
                ->assertOk()
                ->assertJsonPath('status', 'success')
                ->assertJsonPath('data.accepted', false)
                ->assertJsonPath('data.activationId', 'activation-heartbeat-003')
                ->assertJsonPath('data.reasonCode', 'device_mismatch')
                ->assertJsonPath('data.graceUntil', '2026-03-27T20:30:00+00:00');

            $this->assertDatabaseHas('license_heartbeats', [
                'license_activation_id' => $activation->id,
                'reason_code' => 'device_mismatch',
            ]);
            $this->assertSame('machine-primary', $activation->fresh()->machine_id);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_it_rejects_incomplete_heartbeat_payloads(): void
    {
        $response = $this->postJson('/api/v1/licenses/heartbeat', []);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'validation_error')
            ->assertJsonPath('error.reasonCode', 'validation_failed')
            ->assertJsonPath('error.details.activationId.0', 'The activation id field is required.')
            ->assertJsonPath('error.details.activationToken.0', 'The activation token field is required.')
            ->assertJsonPath('error.details.appId.0', 'The app id field is required.')
            ->assertJsonPath('error.details.appVersion.0', 'The app version field is required.')
            ->assertJsonPath('error.details.machineId.0', 'The machine id field is required.')
            ->assertJsonPath('error.details.installationId.0', 'The installation id field is required.');
    }

    public function test_it_returns_expired_license_state_for_heartbeat_attempts(): void
    {
        $app = App::factory()->create(['app_id' => 'xargo.desktop']);
        $plan = LicensePlan::factory()->create(['app_id' => $app->id]);
        $license = License::factory()->create([
            'app_id' => $app->id,
            'plan_id' => $plan->id,
            'status' => LicenseStatus::EXPIRED,
        ]);
        $plainToken = str_repeat('q', 80);
        $activation = LicenseActivation::factory()->create([
            'license_id' => $license->id,
            'activation_id' => 'activation-heartbeat-expired',
            'machine_id' => 'machine-001',
            'installation_id' => 'installation-001',
            'activation_token_hash' => hash('sha256', $plainToken),
            'status' => LicenseActivationStatus::ACTIVE,
        ]);

        $response = $this->postJson('/api/v1/licenses/heartbeat', [
            'activationId' => 'activation-heartbeat-expired',
            'activationToken' => $plainToken,
            'appId' => 'xargo.desktop',
            'appVersion' => '2.2.0',
            'machineId' => 'machine-001',
            'installationId' => 'installation-001',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.accepted', false)
            ->assertJsonPath('data.activationId', $activation->activation_id)
            ->assertJsonPath('data.licenseStatus', 'expired')
            ->assertJsonPath('data.reasonCode', 'license_expired');

        $this->assertDatabaseMissing('license_heartbeats', [
            'license_activation_id' => $activation->id,
        ]);
    }
}

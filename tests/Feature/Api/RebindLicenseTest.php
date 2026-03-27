<?php

namespace Tests\Feature\Api;

use App\Actions\Licensing\ManualRebindLicenseActivationAction;
use App\Domain\Licensing\LicenseActivationStatus;
use App\Models\App;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\LicensePlan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RebindLicenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_marks_a_mismatch_rebind_request_for_manual_review_without_reassigning_the_activation(): void
    {
        Carbon::setTestNow('2026-03-27 22:00:00');

        try {
            $app = App::factory()->create(['app_id' => 'xargo.desktop']);
            $plan = LicensePlan::factory()->create(['app_id' => $app->id]);
            $license = License::factory()->create([
                'app_id' => $app->id,
                'plan_id' => $plan->id,
                'license_key' => 'XARGO-REBIND-001',
            ]);
            $plainToken = str_repeat('g', 80);
            $activation = LicenseActivation::factory()->create([
                'license_id' => $license->id,
                'machine_id' => 'machine-primary',
                'installation_id' => 'installation-primary',
                'activation_token_hash' => hash('sha256', $plainToken),
                'status' => LicenseActivationStatus::ACTIVE,
                'grace_until' => null,
            ]);

            $response = $this->postJson('/api/v1/licenses/rebind/request', [
                'licenseKey' => 'XARGO-REBIND-001',
                'activationToken' => $plainToken,
                'appId' => 'xargo.desktop',
                'appVersion' => '2.3.0',
                'machineId' => 'machine-replacement',
                'installationId' => 'installation-replacement',
            ]);

            $response
                ->assertOk()
                ->assertJsonPath('status', 'success')
                ->assertJsonPath('data.requested', true)
                ->assertJsonPath('data.requiresManualReview', true)
                ->assertJsonPath('data.activationId', $activation->activation_id)
                ->assertJsonPath('data.licenseStatus', 'active')
                ->assertJsonPath('data.reasonCode', 'device_mismatch');

            $this->assertNotNull($response->json('data.graceUntil'));
            $this->assertSame('machine-primary', $activation->fresh()->machine_id);
            $this->assertSame('installation-primary', $activation->fresh()->installation_id);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_rebind_confirm_remains_false_until_a_manual_rebind_has_occurred(): void
    {
        $app = App::factory()->create(['app_id' => 'xargo.desktop']);
        $plan = LicensePlan::factory()->create(['app_id' => $app->id]);
        $license = License::factory()->create([
            'app_id' => $app->id,
            'plan_id' => $plan->id,
            'license_key' => 'XARGO-REBIND-002',
        ]);
        $plainToken = str_repeat('h', 80);
        $activation = LicenseActivation::factory()->create([
            'license_id' => $license->id,
            'machine_id' => 'machine-primary',
            'installation_id' => 'installation-primary',
            'activation_token_hash' => hash('sha256', $plainToken),
            'status' => LicenseActivationStatus::ACTIVE,
            'grace_until' => Carbon::parse('2026-03-27 22:05:00'),
            'last_reason_code' => 'device_mismatch',
        ]);

        $response = $this->postJson('/api/v1/licenses/rebind/confirm', [
            'licenseKey' => 'XARGO-REBIND-002',
            'activationToken' => $plainToken,
            'appId' => 'xargo.desktop',
            'appVersion' => '2.3.0',
            'machineId' => 'machine-replacement',
            'installationId' => 'installation-replacement',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.confirmed', false)
            ->assertJsonPath('data.activationId', $activation->activation_id)
            ->assertJsonPath('data.reasonCode', 'rebind_pending_manual_confirmation');

        $this->assertSame('machine-primary', $activation->fresh()->machine_id);
    }

    public function test_rebind_confirm_succeeds_after_manual_rebind_has_changed_the_bound_device(): void
    {
        Carbon::setTestNow('2026-03-27 22:30:00');

        try {
            $app = App::factory()->create(['app_id' => 'xargo.desktop']);
            $plan = LicensePlan::factory()->create(['app_id' => $app->id]);
            $license = License::factory()->create([
                'app_id' => $app->id,
                'plan_id' => $plan->id,
                'license_key' => 'XARGO-REBIND-003',
            ]);
            $plainToken = str_repeat('i', 80);
            $activation = LicenseActivation::factory()->create([
                'license_id' => $license->id,
                'machine_id' => 'machine-primary',
                'installation_id' => 'installation-primary',
                'activation_token_hash' => hash('sha256', $plainToken),
                'status' => LicenseActivationStatus::ACTIVE,
                'grace_until' => Carbon::parse('2026-03-27 22:10:00'),
                'last_reason_code' => 'device_mismatch',
            ]);

            app(ManualRebindLicenseActivationAction::class)->execute($activation, [
                'machine_id' => 'machine-replacement',
                'installation_id' => 'installation-replacement',
                'device_label' => 'Replacement Device',
            ]);

            $response = $this->postJson('/api/v1/licenses/rebind/confirm', [
                'licenseKey' => 'XARGO-REBIND-003',
                'activationToken' => $plainToken,
                'appId' => 'xargo.desktop',
                'appVersion' => '2.3.0',
                'machineId' => 'machine-replacement',
                'installationId' => 'installation-replacement',
            ]);

            $response
                ->assertOk()
                ->assertJsonPath('status', 'success')
                ->assertJsonPath('data.confirmed', true)
                ->assertJsonPath('data.activationId', $activation->activation_id)
                ->assertJsonPath('data.licenseStatus', 'active')
                ->assertJsonPath('data.graceUntil', null)
                ->assertJsonPath('data.reasonCode', null);

            $this->assertSame('machine-replacement', $activation->fresh()->machine_id);
            $this->assertSame('installation-replacement', $activation->fresh()->installation_id);
            $this->assertNull($activation->fresh()->last_reason_code);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_it_rejects_incomplete_rebind_payloads(): void
    {
        $response = $this->postJson('/api/v1/licenses/rebind/request', []);

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
}

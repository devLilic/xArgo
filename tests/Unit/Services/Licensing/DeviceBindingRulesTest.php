<?php

namespace Tests\Unit\Services\Licensing;

use App\Actions\Licensing\ManualRebindLicenseActivationAction;
use App\Domain\Licensing\LicenseActivationStatus;
use App\Models\License;
use App\Services\Licensing\AntiClonePolicyService;
use App\Services\Licensing\LicenseActivationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceBindingRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_activation_on_a_valid_license_creates_the_bound_device_record(): void
    {
        $service = app(LicenseActivationService::class);
        $license = License::factory()->create();

        $result = $service->activateFirstDevice(
            license: $license,
            machineId: 'machine-primary',
            installationId: 'install-primary',
            deviceLabel: 'Primary Device',
        );

        $this->assertSame(1, $service->countActiveDevices($license));
        $this->assertSame(LicenseActivationStatus::ACTIVE, $result->activation->status);
        $this->assertSame('machine-primary', $result->activation->machine_id);
        $this->assertSame('install-primary', $result->activation->installation_id);
        $this->assertTrue($result->activation->matchesActivationToken($result->plainTextToken));
    }

    public function test_second_device_attempt_produces_device_mismatch_reason(): void
    {
        $activationService = app(LicenseActivationService::class);
        $antiClonePolicy = app(AntiClonePolicyService::class);
        $license = License::factory()->create();

        $firstActivation = $activationService->activateFirstDevice(
            license: $license,
            machineId: 'machine-primary',
            installationId: 'install-primary',
        )->activation;

        $decision = $antiClonePolicy->evaluate(
            $firstActivation,
            'machine-clone',
            'install-clone',
            Carbon::parse('2026-03-27 18:00:00'),
        );

        $this->assertTrue($decision->allowed);
        $this->assertFalse($decision->matchesBoundDevice);
        $this->assertSame('device_mismatch', $decision->reasonCode);
        $this->assertNotNull($decision->graceUntil);
    }

    public function test_device_mismatch_has_grace_then_blocks(): void
    {
        $activationService = app(LicenseActivationService::class);
        $antiClonePolicy = app(AntiClonePolicyService::class);
        $license = License::factory()->create();

        $activation = $activationService->activateFirstDevice(
            license: $license,
            machineId: 'machine-primary',
            installationId: 'install-primary',
        )->activation;

        $graceDecision = $antiClonePolicy->evaluate(
            $activation,
            'machine-clone',
            'install-clone',
            Carbon::parse('2026-03-27 18:00:00'),
        );

        $activation->update([
            'grace_until' => $graceDecision->graceUntil,
            'last_reason_code' => $graceDecision->reasonCode,
        ]);

        $blockedDecision = $antiClonePolicy->evaluate(
            $activation->fresh(),
            'machine-clone',
            'install-clone',
            Carbon::parse('2026-03-27 18:10:00'),
        );

        $this->assertTrue($graceDecision->allowed);
        $this->assertFalse($graceDecision->blocked);
        $this->assertFalse($blockedDecision->allowed);
        $this->assertTrue($blockedDecision->blocked);
        $this->assertSame('device_mismatch', $blockedDecision->reasonCode);
    }

    public function test_original_activation_is_not_auto_invalidated_by_mismatch_attempts(): void
    {
        $activationService = app(LicenseActivationService::class);
        $antiClonePolicy = app(AntiClonePolicyService::class);
        $license = License::factory()->create();

        $activation = $activationService->activateFirstDevice(
            license: $license,
            machineId: 'machine-primary',
            installationId: 'install-primary',
        )->activation;

        $mismatchDecision = $antiClonePolicy->evaluate(
            $activation,
            'machine-clone',
            'install-clone',
            Carbon::parse('2026-03-27 18:00:00'),
        );

        $originalDecision = $antiClonePolicy->evaluate(
            $activation->fresh(),
            'machine-primary',
            'install-primary',
            Carbon::parse('2026-03-27 18:01:00'),
        );

        $this->assertSame(LicenseActivationStatus::ACTIVE, $activation->fresh()->status);
        $this->assertTrue($mismatchDecision->allowed);
        $this->assertTrue($originalDecision->allowed);
        $this->assertTrue($originalDecision->matchesBoundDevice);
    }

    public function test_manual_rebind_intentionally_changes_the_bound_device(): void
    {
        $activationService = app(LicenseActivationService::class);
        $antiClonePolicy = app(AntiClonePolicyService::class);
        $manualRebind = app(ManualRebindLicenseActivationAction::class);
        $license = License::factory()->create();

        $activation = $activationService->activateFirstDevice(
            license: $license,
            machineId: 'machine-primary',
            installationId: 'install-primary',
            deviceLabel: 'Primary Device',
        )->activation;

        $reboundActivation = $manualRebind->execute($activation, [
            'machine_id' => 'machine-rebound',
            'installation_id' => 'install-rebound',
            'device_label' => 'Replacement Device',
        ]);

        $oldDeviceDecision = $antiClonePolicy->evaluate(
            $reboundActivation,
            'machine-primary',
            'install-primary',
            Carbon::parse('2026-03-27 19:00:00'),
        );

        $newDeviceDecision = $antiClonePolicy->evaluate(
            $reboundActivation,
            'machine-rebound',
            'install-rebound',
            Carbon::parse('2026-03-27 19:00:00'),
        );

        $this->assertSame(1, $activationService->countActiveDevices($license));
        $this->assertSame('machine-rebound', $reboundActivation->machine_id);
        $this->assertSame('install-rebound', $reboundActivation->installation_id);
        $this->assertFalse($oldDeviceDecision->matchesBoundDevice);
        $this->assertSame('device_mismatch', $oldDeviceDecision->reasonCode);
        $this->assertTrue($newDeviceDecision->allowed);
        $this->assertTrue($newDeviceDecision->matchesBoundDevice);
        $this->assertSame(LicenseActivationStatus::ACTIVE, $reboundActivation->status);
    }
}

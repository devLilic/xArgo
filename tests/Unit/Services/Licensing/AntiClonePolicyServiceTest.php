<?php

namespace Tests\Unit\Services\Licensing;

use App\Domain\Licensing\LicenseActivationStatus;
use App\Models\LicenseActivation;
use App\Services\Licensing\AntiClonePolicyService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AntiClonePolicyServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_allows_the_bound_device_without_reason_code(): void
    {
        $service = app(AntiClonePolicyService::class);
        $activation = LicenseActivation::factory()->create([
            'machine_id' => 'machine-a',
            'installation_id' => 'install-a',
            'status' => LicenseActivationStatus::ACTIVE,
        ]);

        $decision = $service->evaluate($activation, 'machine-a', 'install-a');

        $this->assertTrue($decision->allowed);
        $this->assertTrue($decision->matchesBoundDevice);
        $this->assertFalse($decision->blocked);
        $this->assertNull($decision->reasonCode);
        $this->assertNull($decision->graceUntil);
    }

    public function test_it_grants_short_grace_for_a_device_mismatch_when_no_grace_has_started(): void
    {
        $service = app(AntiClonePolicyService::class);
        $now = Carbon::parse('2026-03-27 16:00:00');
        $activation = LicenseActivation::factory()->create([
            'machine_id' => 'machine-a',
            'installation_id' => 'install-a',
            'grace_until' => null,
            'status' => LicenseActivationStatus::ACTIVE,
        ]);

        $decision = $service->evaluate($activation, 'machine-b', 'install-b', $now);

        $this->assertTrue($decision->allowed);
        $this->assertFalse($decision->matchesBoundDevice);
        $this->assertFalse($decision->blocked);
        $this->assertSame('device_mismatch', $decision->reasonCode);
        $this->assertTrue(
            $now->copy()->addSeconds((int) config('licensing.device_mismatch.grace_period_seconds'))
                ->equalTo($decision->graceUntil)
        );
        $this->assertSame(LicenseActivationStatus::ACTIVE, $activation->fresh()->status);
    }

    public function test_it_allows_mismatch_only_until_grace_expires_and_then_blocks(): void
    {
        $service = app(AntiClonePolicyService::class);
        $futureGrace = Carbon::parse('2026-03-27 16:05:00');
        $activation = LicenseActivation::factory()->create([
            'machine_id' => 'machine-a',
            'installation_id' => 'install-a',
            'grace_until' => $futureGrace,
            'status' => LicenseActivationStatus::ACTIVE,
        ]);

        $graceDecision = $service->evaluate(
            $activation,
            'machine-b',
            'install-b',
            Carbon::parse('2026-03-27 16:03:00'),
        );

        $blockedDecision = $service->evaluate(
            $activation->fresh(),
            'machine-b',
            'install-b',
            Carbon::parse('2026-03-27 16:06:00'),
        );

        $this->assertTrue($graceDecision->allowed);
        $this->assertFalse($graceDecision->blocked);
        $this->assertTrue($futureGrace->equalTo($graceDecision->graceUntil));

        $this->assertFalse($blockedDecision->allowed);
        $this->assertTrue($blockedDecision->blocked);
        $this->assertSame('device_mismatch', $blockedDecision->reasonCode);
        $this->assertSame(LicenseActivationStatus::ACTIVE, $activation->fresh()->status);
    }
}

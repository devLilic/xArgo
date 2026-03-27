<?php

namespace Tests\Unit\Services\Licensing;

use App\Domain\Licensing\LicenseActivationStatus;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Services\Licensing\LicenseActivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LicenseActivationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_issues_a_plain_and_hashed_activation_token(): void
    {
        $service = app(LicenseActivationService::class);

        $issuedToken = $service->issueActivationToken();

        $this->assertSame(80, strlen($issuedToken->plainTextToken));
        $this->assertSame(hash('sha256', $issuedToken->plainTextToken), $issuedToken->hashedToken);
    }

    public function test_it_creates_the_first_activation_for_a_license(): void
    {
        $service = app(LicenseActivationService::class);
        $license = License::factory()->create();
        $seenAt = Carbon::parse('2026-03-27 15:00:00');

        $result = $service->activateFirstDevice(
            license: $license,
            machineId: 'machine-001',
            installationId: 'install-001',
            deviceLabel: 'Ada Laptop',
            seenAt: $seenAt,
        );

        $this->assertDatabaseHas('license_activations', [
            'id' => $result->activation->id,
            'license_id' => $license->id,
            'machine_id' => 'machine-001',
            'installation_id' => 'install-001',
            'device_label' => 'Ada Laptop',
            'status' => LicenseActivationStatus::ACTIVE->value,
        ]);
        $this->assertTrue($result->activation->matchesActivationToken($result->plainTextToken));
        $this->assertTrue($seenAt->equalTo($result->activation->first_seen_at));
        $this->assertTrue($seenAt->equalTo($result->activation->last_seen_at));
    }

    public function test_it_can_lookup_an_activation_by_activation_id_or_device_fingerprint(): void
    {
        $service = app(LicenseActivationService::class);
        $license = License::factory()->create();
        $activation = LicenseActivation::factory()->create([
            'license_id' => $license->id,
            'activation_id' => 'activation-123',
            'machine_id' => 'machine-123',
            'installation_id' => 'install-123',
        ]);

        $this->assertTrue(
            $service->findActivation($license, activationId: 'activation-123')?->is($activation) ?? false
        );
        $this->assertTrue(
            $service->findActivation($license, machineId: 'machine-123', installationId: 'install-123')?->is($activation) ?? false
        );
    }

    public function test_it_counts_only_active_devices_for_a_license(): void
    {
        $service = app(LicenseActivationService::class);
        $license = License::factory()->create();

        LicenseActivation::factory()->create([
            'license_id' => $license->id,
            'status' => LicenseActivationStatus::ACTIVE,
        ]);
        LicenseActivation::factory()->create([
            'license_id' => $license->id,
            'status' => LicenseActivationStatus::ACTIVE,
        ]);
        LicenseActivation::factory()->create([
            'license_id' => $license->id,
            'status' => LicenseActivationStatus::BLOCKED,
        ]);

        $this->assertSame(2, $service->countActiveDevices($license));
    }
}

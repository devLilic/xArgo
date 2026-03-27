<?php

namespace Tests\Unit\Services\Licensing;

use App\Models\LicenseActivation;
use App\Services\Licensing\LicenseHeartbeatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LicenseHeartbeatServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_a_heartbeat_and_updates_last_seen_at(): void
    {
        $service = app(LicenseHeartbeatService::class);
        $activation = LicenseActivation::factory()->create([
            'last_seen_at' => Carbon::parse('2026-03-27 19:00:00'),
            'last_reason_code' => null,
        ]);
        $receivedAt = Carbon::parse('2026-03-27 20:15:00');

        $heartbeat = $service->recordHeartbeat(
            activation: $activation,
            appVersion: '2.5.0',
            ipAddress: '203.0.113.10',
            reasonCode: 'ok',
            receivedAt: $receivedAt,
        );

        $this->assertDatabaseHas('license_heartbeats', [
            'id' => $heartbeat->id,
            'license_activation_id' => $activation->id,
            'app_version' => '2.5.0',
            'ip_address' => '203.0.113.10',
            'reason_code' => 'ok',
        ]);
        $this->assertTrue($receivedAt->equalTo($heartbeat->received_at));
        $this->assertTrue($receivedAt->equalTo($activation->fresh()->last_seen_at));
        $this->assertSame('ok', $activation->fresh()->last_reason_code);
    }

    public function test_it_persists_null_reason_codes_without_breaking_last_seen_updates(): void
    {
        $service = app(LicenseHeartbeatService::class);
        $activation = LicenseActivation::factory()->create([
            'last_reason_code' => 'device_mismatch',
        ]);
        $receivedAt = Carbon::parse('2026-03-27 21:00:00');

        $heartbeat = $service->recordHeartbeat(
            activation: $activation,
            appVersion: '2.5.1',
            reasonCode: null,
            receivedAt: $receivedAt,
        );

        $this->assertNull($heartbeat->reason_code);
        $this->assertTrue($receivedAt->equalTo($activation->fresh()->last_seen_at));
        $this->assertNull($activation->fresh()->last_reason_code);
    }
}

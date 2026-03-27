<?php

namespace Tests\Feature\Apps;

use App\Models\LicenseActivation;
use App\Models\LicenseHeartbeat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LicenseHeartbeatModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_license_heartbeats_table_exposes_the_required_schema(): void
    {
        $this->assertTrue(Schema::hasTable('license_heartbeats'));
        $this->assertTrue(Schema::hasColumns('license_heartbeats', [
            'license_activation_id',
            'app_version',
            'received_at',
            'ip_address',
            'reason_code',
        ]));
    }

    public function test_heartbeat_belongs_to_an_activation(): void
    {
        $activation = LicenseActivation::factory()->create();
        $receivedAt = Carbon::parse('2026-03-27 20:00:00');

        $heartbeat = LicenseHeartbeat::factory()->create([
            'license_activation_id' => $activation->id,
            'app_version' => '2.4.1',
            'received_at' => $receivedAt,
            'reason_code' => 'ok',
        ]);

        $this->assertTrue($heartbeat->activation->is($activation));
        $this->assertCount(1, $activation->fresh()->heartbeats);
        $this->assertSame('2.4.1', $heartbeat->app_version);
        $this->assertSame('ok', $heartbeat->reason_code);
        $this->assertTrue($receivedAt->equalTo($heartbeat->received_at));
    }
}

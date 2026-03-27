<?php

namespace Tests\Feature\Foundation;

use App\Models\LicenseActivation;
use App\Models\LicenseHeartbeat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PruneLicenseHeartbeatsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_prunes_heartbeat_records_older_than_the_retention_window(): void
    {
        Carbon::setTestNow('2026-03-27 12:00:00');

        try {
            $activation = LicenseActivation::factory()->create();
            $expiredHeartbeat = LicenseHeartbeat::factory()->create([
                'license_activation_id' => $activation->id,
                'received_at' => now()->subDays(4),
            ]);
            $retainedHeartbeat = LicenseHeartbeat::factory()->create([
                'license_activation_id' => $activation->id,
                'received_at' => now()->subDays(2),
            ]);

            $this->artisan('licensing:prune-heartbeats')
                ->expectsOutput('Pruned 1 expired heartbeat records.')
                ->assertSuccessful();

            $this->assertDatabaseMissing('license_heartbeats', [
                'id' => $expiredHeartbeat->id,
            ]);
            $this->assertDatabaseHas('license_heartbeats', [
                'id' => $retainedHeartbeat->id,
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }
}

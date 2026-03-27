<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicensingApiRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_licensing_api_routes_are_rate_limited(): void
    {
        config()->set('licensing.api.rate_limit_per_minute', 2);

        $this->getJson('/api/v1/licenses/ping')
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->getJson('/api/v1/licenses/ping')
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->getJson('/api/v1/licenses/ping')
            ->assertStatus(429)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('data', null)
            ->assertJsonPath('error.code', 'rate_limited')
            ->assertJsonPath('error.reasonCode', 'rate_limited');
    }
}

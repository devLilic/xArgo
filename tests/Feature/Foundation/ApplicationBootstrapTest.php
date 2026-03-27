<?php

namespace Tests\Feature\Foundation;

use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ApplicationBootstrapTest extends TestCase
{
    public function test_admin_shell_renders_successfully(): void
    {
        $this->withoutVite();

        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Dashboard')
                ->where('appName', config('app.name'))
                ->where('environment', 'testing')
            );
    }

    public function test_api_ping_endpoint_returns_success_payload(): void
    {
        $response = $this->getJson('/api/v1/ping');

        $response
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('app', config('app.name'));
    }
}

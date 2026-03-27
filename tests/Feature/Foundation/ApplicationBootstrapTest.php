<?php

namespace Tests\Feature\Foundation;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ApplicationBootstrapTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_shell_renders_successfully(): void
    {
        $this->withoutVite();

        $response = $this->actingAs(User::factory()->create())->get('/');

        $response
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Dashboard')
                ->where('appName', config('app.name'))
                ->where('environment', 'testing')
            );
    }

    public function test_admin_root_redirects_guests_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_api_ping_endpoint_returns_success_payload(): void
    {
        $response = $this->getJson('/api/v1/licenses/ping');

        $response
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('app', config('app.name'));
    }

    public function test_route_names_are_scoped_for_admin_and_api_v1(): void
    {
        $this->assertSame('/', route('admin.dashboard', absolute: false));
        $this->assertSame('/api/v1/licenses/ping', route('api.v1.licenses.ping', absolute: false));
        $this->assertSame('/login', route('login', absolute: false));
        $this->assertSame('/forgot-password', route('password.request', absolute: false));
    }
}

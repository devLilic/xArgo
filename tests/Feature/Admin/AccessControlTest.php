<?php

namespace Tests\Feature\Admin;

use App\Domain\Auth\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_read_only_users_can_view_the_admin_dashboard(): void
    {
        $this->withoutVite();

        $user = User::factory()->readOnly()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_read_only_users_cannot_send_invitations(): void
    {
        $user = User::factory()->readOnly()->create();

        $this->actingAs($user)
            ->post(route('admin.invitations.store'), [
                'email' => 'blocked@example.com',
            ])
            ->assertForbidden();
    }

    public function test_support_users_can_send_invitations(): void
    {
        $user = User::factory()->support()->create();

        $this->actingAs($user)
            ->post(route('admin.invitations.store'), [
                'email' => 'support-invite@example.com',
            ])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_super_admin_users_can_send_invitations(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->post(route('admin.invitations.store'), [
                'email' => 'owner-invite@example.com',
            ])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_newly_invited_users_default_to_read_only_role(): void
    {
        $user = User::factory()->create();

        $this->assertSame(Role::READ_ONLY, $user->role);
    }

    public function test_guests_are_redirected_from_protected_admin_routes(): void
    {
        $target = User::factory()->readOnly()->create();

        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.users.index'))
            ->assertRedirect(route('login'));

        $this->patch(route('admin.users.role.update', $target), [
            'role' => Role::SUPPORT->value,
        ])->assertRedirect(route('login'));
    }

    public function test_read_only_users_cannot_change_roles(): void
    {
        $user = User::factory()->readOnly()->create();
        $target = User::factory()->support()->create();

        $this->actingAs($user)
            ->patch(route('admin.users.role.update', $target), [
                'role' => Role::SUPER_ADMIN->value,
            ])
            ->assertForbidden();
    }

    public function test_support_users_cannot_toggle_user_activity(): void
    {
        $user = User::factory()->support()->create();
        $target = User::factory()->readOnly()->create();

        $this->actingAs($user)
            ->patch(route('admin.users.activity.update', $target), [
                'active' => false,
            ])
            ->assertForbidden();
    }
}

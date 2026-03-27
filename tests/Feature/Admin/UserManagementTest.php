<?php

namespace Tests\Feature\Admin;

use App\Domain\Auth\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_support_users_can_view_the_user_list(): void
    {
        $this->withoutVite();

        $support = User::factory()->support()->create();
        User::factory()->count(2)->create();

        $this->actingAs($support)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Users/Index')
                ->has('users', 3)
            );
    }

    public function test_read_only_users_can_view_a_user_profile(): void
    {
        $this->withoutVite();

        $viewer = User::factory()->readOnly()->create();
        $target = User::factory()->support()->create();

        $this->actingAs($viewer)
            ->get(route('admin.users.show', $target))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Users/Show')
                ->where('managedUser.email', $target->email)
            );
    }

    public function test_super_admin_can_change_a_users_role(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $target = User::factory()->readOnly()->create();

        $this->actingAs($superAdmin)
            ->patch(route('admin.users.role.update', $target), [
                'role' => Role::SUPPORT->value,
            ])
            ->assertRedirect(route('admin.users.show', $target))
            ->assertSessionHas('status', 'User role updated.');

        $this->assertSame(Role::SUPPORT, $target->fresh()->role);
    }

    public function test_support_users_cannot_change_roles(): void
    {
        $support = User::factory()->support()->create();
        $target = User::factory()->readOnly()->create();

        $this->actingAs($support)
            ->patch(route('admin.users.role.update', $target), [
                'role' => Role::SUPPORT->value,
            ])
            ->assertForbidden();
    }

    public function test_super_admin_can_deactivate_and_reactivate_a_user(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $target = User::factory()->support()->create();

        $this->actingAs($superAdmin)
            ->patch(route('admin.users.activity.update', $target), [
                'active' => false,
            ])
            ->assertRedirect(route('admin.users.show', $target))
            ->assertSessionHas('status', 'User deactivated.');

        $this->assertNotNull($target->fresh()->deactivated_at);

        $this->actingAs($superAdmin)
            ->patch(route('admin.users.activity.update', $target), [
                'active' => true,
            ])
            ->assertRedirect(route('admin.users.show', $target))
            ->assertSessionHas('status', 'User reactivated.');

        $this->assertNull($target->fresh()->deactivated_at);
    }

    public function test_deactivated_users_cannot_log_in(): void
    {
        $user = User::factory()->readOnly()->deactivated()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Domain\Auth\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitation_creation_is_audited(): void
    {
        $actor = User::factory()->superAdmin()->create();

        $this->actingAs($actor)
            ->post(route('admin.invitations.store'), [
                'email' => 'audit-invite@example.com',
            ])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $actor->id,
            'event' => 'admin.invitation.created',
            'target_type' => 'user_invitation',
        ]);
    }

    public function test_role_change_is_audited(): void
    {
        $actor = User::factory()->superAdmin()->create();
        $target = User::factory()->readOnly()->create();

        $this->actingAs($actor)
            ->patch(route('admin.users.role.update', $target), [
                'role' => Role::SUPPORT->value,
            ])
            ->assertRedirect(route('admin.users.show', $target));

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $actor->id,
            'event' => 'admin.user.role_changed',
            'target_type' => 'user',
            'target_id' => $target->id,
        ]);
    }

    public function test_user_deactivation_and_reactivation_are_audited(): void
    {
        $actor = User::factory()->superAdmin()->create();
        $target = User::factory()->support()->create();

        $this->actingAs($actor)
            ->patch(route('admin.users.activity.update', $target), [
                'active' => false,
            ])
            ->assertRedirect(route('admin.users.show', $target));

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $actor->id,
            'event' => 'admin.user.deactivated',
            'target_type' => 'user',
            'target_id' => $target->id,
        ]);

        $this->actingAs($actor)
            ->patch(route('admin.users.activity.update', $target), [
                'active' => true,
            ])
            ->assertRedirect(route('admin.users.show', $target));

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $actor->id,
            'event' => 'admin.user.reactivated',
            'target_type' => 'user',
            'target_id' => $target->id,
        ]);
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Domain\Auth\Role;
use App\Domain\Licensing\LicenseStatus;
use App\Models\App;
use App\Models\License;
use App\Models\LicensePlan;
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

    public function test_license_creation_is_audited(): void
    {
        $actor = User::factory()->superAdmin()->create();
        $app = App::factory()->create();
        $plan = LicensePlan::factory()->create([
            'app_id' => $app->id,
        ]);

        $this->actingAs($actor)
            ->post(route('admin.licenses.store'), [
                'app_id' => $app->id,
                'plan_id' => $plan->id,
                'customer_name' => 'Audit License User',
                'customer_email' => 'license-audit@example.com',
                'status' => LicenseStatus::ACTIVE->value,
                'max_devices' => 2,
                'grace_hours' => 4,
                'notes' => 'Audit create',
            ])
            ->assertRedirect();

        $license = License::query()->where('customer_email', 'license-audit@example.com')->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $actor->id,
            'event' => 'admin.license.created',
            'target_type' => 'license',
            'target_id' => $license->id,
        ]);
    }

    public function test_license_update_is_audited(): void
    {
        $actor = User::factory()->superAdmin()->create();
        $app = App::factory()->create();
        $plan = LicensePlan::factory()->create([
            'app_id' => $app->id,
        ]);
        $license = License::factory()->create([
            'app_id' => $app->id,
            'plan_id' => $plan->id,
            'customer_email' => 'before-update@example.com',
        ]);

        $this->actingAs($actor)
            ->patch(route('admin.licenses.update', $license->id), [
                'app_id' => $app->id,
                'plan_id' => $plan->id,
                'customer_name' => 'Updated License User',
                'customer_email' => 'after-update@example.com',
                'max_devices' => 6,
                'grace_hours' => 10,
                'notes' => 'Updated by audit test',
            ])
            ->assertRedirect(route('admin.licenses.edit', $license->id));

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $actor->id,
            'event' => 'admin.license.updated',
            'target_type' => 'license',
            'target_id' => $license->id,
        ]);
    }

    public function test_license_status_transitions_are_audited(): void
    {
        $actor = User::factory()->superAdmin()->create();
        $license = License::factory()->create();

        $this->actingAs($actor)
            ->patch(route('admin.licenses.status.update', $license->id), [
                'status_action' => 'suspend',
            ])
            ->assertRedirect(route('admin.licenses.show', $license->id));

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $actor->id,
            'event' => 'admin.license.suspended',
            'target_type' => 'license',
            'target_id' => $license->id,
        ]);

        $this->actingAs($actor)
            ->patch(route('admin.licenses.status.update', $license->id), [
                'status_action' => 'revoke',
            ])
            ->assertRedirect(route('admin.licenses.show', $license->id));

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $actor->id,
            'event' => 'admin.license.revoked',
            'target_type' => 'license',
            'target_id' => $license->id,
        ]);

        $this->actingAs($actor)
            ->patch(route('admin.licenses.status.update', $license->id), [
                'status_action' => 'reactivate',
            ])
            ->assertRedirect(route('admin.licenses.show', $license->id));

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $actor->id,
            'event' => 'admin.license.reactivated',
            'target_type' => 'license',
            'target_id' => $license->id,
        ]);
    }

    public function test_license_restore_is_audited(): void
    {
        $actor = User::factory()->superAdmin()->create();
        $license = License::factory()->create();
        $license->delete();

        $this->actingAs($actor)
            ->patch(route('admin.licenses.restore', $license->id))
            ->assertRedirect(route('admin.licenses.show', $license->id));

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $actor->id,
            'event' => 'admin.license.restored',
            'target_type' => 'license',
            'target_id' => $license->id,
        ]);
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Domain\Licensing\LicenseStatus;
use App\Models\App;
use App\Models\License;
use App\Models\LicensePlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class LicenseManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_support_users_can_view_license_pages_and_filters(): void
    {
        $this->withoutVite();

        $support = User::factory()->support()->create();
        $desktopApp = App::factory()->create(['name' => 'Desktop']);
        $mobileApp = App::factory()->create(['name' => 'Mobile']);
        $desktopPlan = LicensePlan::factory()->create(['app_id' => $desktopApp->id]);
        $mobilePlan = LicensePlan::factory()->create(['app_id' => $mobileApp->id]);

        $visibleLicense = License::factory()->create([
            'app_id' => $desktopApp->id,
            'plan_id' => $desktopPlan->id,
            'license_key' => 'XARGO-FIND-ME-0001',
            'customer_email' => 'findme@example.com',
            'status' => LicenseStatus::ACTIVE,
        ]);

        License::factory()->create([
            'app_id' => $mobileApp->id,
            'plan_id' => $mobilePlan->id,
            'license_key' => 'XARGO-HIDE-ME-0002',
            'customer_email' => 'hidden@example.com',
            'status' => LicenseStatus::SUSPENDED,
        ]);

        $this->actingAs($support)
            ->get(route('admin.licenses.index', [
                'license_key' => 'FIND-ME',
                'customer_email' => 'findme@example.com',
                'app_id' => $desktopApp->id,
                'status' => LicenseStatus::ACTIVE->value,
            ]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Licenses/Index')
                ->has('licenses', 1)
                ->where('licenses.0.id', $visibleLicense->id)
                ->where('can.create', false)
            );

        $this->actingAs($support)
            ->get(route('admin.licenses.show', $visibleLicense->id))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Licenses/Show')
                ->where('managedLicense.id', $visibleLicense->id)
                ->where('can.update', false)
            );

        $this->actingAs($support)
            ->get(route('admin.licenses.edit', $visibleLicense->id))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Licenses/Edit')
                ->where('managedLicense.id', $visibleLicense->id)
                ->where('can.update', false)
            );
    }

    public function test_super_admin_can_create_edit_transition_archive_and_restore_licenses(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $app = App::factory()->create();
        $plan = LicensePlan::factory()->create([
            'app_id' => $app->id,
            'default_max_devices' => 2,
        ]);

        $this->actingAs($superAdmin)
            ->post(route('admin.licenses.store'), [
                'app_id' => $app->id,
                'plan_id' => $plan->id,
                'customer_name' => 'Grace Hopper',
                'customer_email' => 'grace@example.com',
                'status' => LicenseStatus::ACTIVE->value,
                'max_devices' => 4,
                'expires_at' => '2030-01-01 12:00:00',
                'grace_hours' => 8,
                'notes' => 'Priority account',
            ])
            ->assertRedirect();

        $license = License::query()->where('customer_email', 'grace@example.com')->firstOrFail();

        $this->assertNotEmpty($license->license_key);
        $this->assertNotEmpty($license->public_key);

        $this->actingAs($superAdmin)
            ->patch(route('admin.licenses.update', $license->id), [
                'app_id' => $app->id,
                'plan_id' => $plan->id,
                'customer_name' => 'Rear Admiral Grace Hopper',
                'customer_email' => 'grace@example.com',
                'max_devices' => 5,
                'expires_at' => '2031-02-02 14:30:00',
                'grace_hours' => 12,
                'notes' => 'Updated note',
            ])
            ->assertRedirect(route('admin.licenses.edit', $license->id))
            ->assertSessionHas('status', 'License updated.');

        $this->assertDatabaseHas('licenses', [
            'id' => $license->id,
            'customer_name' => 'Rear Admiral Grace Hopper',
            'max_devices' => 5,
            'grace_hours' => 12,
            'notes' => 'Updated note',
        ]);

        $this->actingAs($superAdmin)
            ->patch(route('admin.licenses.status.update', $license->id), [
                'status_action' => 'suspend',
            ])
            ->assertRedirect(route('admin.licenses.show', $license->id));

        $this->assertSame(LicenseStatus::SUSPENDED, $license->fresh()->status);

        $this->actingAs($superAdmin)
            ->patch(route('admin.licenses.status.update', $license->id), [
                'status_action' => 'revoke',
            ])
            ->assertRedirect(route('admin.licenses.show', $license->id));

        $this->assertSame(LicenseStatus::REVOKED, $license->fresh()->status);

        $this->actingAs($superAdmin)
            ->patch(route('admin.licenses.status.update', $license->id), [
                'status_action' => 'reactivate',
            ])
            ->assertRedirect(route('admin.licenses.show', $license->id));

        $this->assertSame(LicenseStatus::ACTIVE, $license->fresh()->status);

        $this->actingAs($superAdmin)
            ->delete(route('admin.licenses.destroy', $license->id))
            ->assertRedirect(route('admin.licenses.index'))
            ->assertSessionHas('status', 'License archived.');

        $this->assertSoftDeleted('licenses', ['id' => $license->id]);

        $this->actingAs($superAdmin)
            ->patch(route('admin.licenses.restore', $license->id))
            ->assertRedirect(route('admin.licenses.show', $license->id))
            ->assertSessionHas('status', 'License restored.');

        $this->assertNull($license->fresh()->deleted_at);
    }

    public function test_support_users_cannot_mutate_licenses(): void
    {
        $support = User::factory()->support()->create();
        $app = App::factory()->create();
        $plan = LicensePlan::factory()->create(['app_id' => $app->id]);
        $license = License::factory()->create([
            'app_id' => $app->id,
            'plan_id' => $plan->id,
        ]);

        $this->actingAs($support)
            ->post(route('admin.licenses.store'), [
                'app_id' => $app->id,
                'plan_id' => $plan->id,
                'status' => LicenseStatus::ACTIVE->value,
                'max_devices' => 1,
                'grace_hours' => 1,
            ])
            ->assertForbidden();

        $this->actingAs($support)
            ->patch(route('admin.licenses.update', $license->id), [
                'app_id' => $app->id,
                'plan_id' => $plan->id,
                'max_devices' => 1,
                'grace_hours' => 1,
            ])
            ->assertForbidden();

        $this->actingAs($support)
            ->patch(route('admin.licenses.status.update', $license->id), [
                'status_action' => 'suspend',
            ])
            ->assertForbidden();

        $this->actingAs($support)
            ->delete(route('admin.licenses.destroy', $license->id))
            ->assertForbidden();

        $this->actingAs($support)
            ->patch(route('admin.licenses.restore', $license->id))
            ->assertForbidden();
    }
}

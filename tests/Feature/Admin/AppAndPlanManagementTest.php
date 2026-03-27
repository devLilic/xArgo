<?php

namespace Tests\Feature\Admin;

use App\Domain\Licensing\LicenseDurationType;
use App\Models\App;
use App\Models\LicensePlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class AppAndPlanManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_support_users_can_view_app_and_plan_indexes(): void
    {
        $this->withoutVite();

        $user = User::factory()->support()->create();
        $app = App::factory()->create();
        LicensePlan::factory()->create([
            'app_id' => $app->id,
        ]);

        $this->actingAs($user)
            ->get(route('admin.apps.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Apps/Index')
                ->has('apps', 1)
                ->where('can.create', false)
            );

        $this->actingAs($user)
            ->get(route('admin.plans.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Plans/Index')
                ->has('plans', 1)
                ->has('apps', 1)
                ->where('can.create', false)
            );
    }

    public function test_super_admin_can_create_and_edit_apps(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->post(route('admin.apps.store'), [
                'name' => 'X Argo Desktop',
                'slug' => 'x-argo-desktop',
                'app_id' => 'com.xargo.desktop',
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.apps.index'))
            ->assertSessionHas('status', 'Application created.');

        $app = App::query()->where('app_id', 'com.xargo.desktop')->firstOrFail();

        $this->actingAs($superAdmin)
            ->patch(route('admin.apps.update', $app), [
                'name' => 'X Argo Studio',
                'slug' => 'x-argo-studio',
                'app_id' => 'com.xargo.studio',
                'is_active' => false,
            ])
            ->assertRedirect(route('admin.apps.edit', $app))
            ->assertSessionHas('status', 'Application updated.');

        $this->assertDatabaseHas('apps', [
            'id' => $app->id,
            'name' => 'X Argo Studio',
            'slug' => 'x-argo-studio',
            'app_id' => 'com.xargo.studio',
            'is_active' => false,
        ]);
    }

    public function test_super_admin_can_create_and_edit_license_plans(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $app = App::factory()->create();

        $this->actingAs($superAdmin)
            ->post(route('admin.plans.store'), [
                'app_id' => $app->id,
                'name' => 'Pro Monthly',
                'code' => 'PRO-MONTHLY',
                'duration_type' => LicenseDurationType::SUBSCRIPTION->value,
                'duration_days' => 30,
                'default_max_devices' => 3,
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.plans.index'))
            ->assertSessionHas('status', 'Plan created.');

        $plan = LicensePlan::query()->where('code', 'PRO-MONTHLY')->firstOrFail();

        $this->actingAs($superAdmin)
            ->patch(route('admin.plans.update', $plan), [
                'app_id' => $app->id,
                'name' => 'Pro Lifetime',
                'code' => 'PRO-LIFETIME',
                'duration_type' => LicenseDurationType::PERMANENT->value,
                'duration_days' => 90,
                'default_max_devices' => 2,
                'is_active' => false,
            ])
            ->assertRedirect(route('admin.plans.edit', $plan))
            ->assertSessionHas('status', 'Plan updated.');

        $this->assertDatabaseHas('license_plans', [
            'id' => $plan->id,
            'name' => 'Pro Lifetime',
            'code' => 'PRO-LIFETIME',
            'duration_type' => LicenseDurationType::PERMANENT->value,
            'duration_days' => null,
            'default_max_devices' => 2,
            'is_active' => false,
        ]);
    }

    public function test_support_users_cannot_create_or_edit_apps_and_plans(): void
    {
        $support = User::factory()->support()->create();
        $app = App::factory()->create();
        $plan = LicensePlan::factory()->create([
            'app_id' => $app->id,
        ]);

        $this->actingAs($support)
            ->post(route('admin.apps.store'), [
                'name' => 'Blocked App',
                'slug' => 'blocked-app',
                'app_id' => 'com.xargo.blocked',
                'is_active' => true,
            ])
            ->assertForbidden();

        $this->actingAs($support)
            ->patch(route('admin.apps.update', $app), [
                'name' => $app->name,
                'slug' => $app->slug,
                'app_id' => $app->app_id,
                'is_active' => $app->is_active,
            ])
            ->assertForbidden();

        $this->actingAs($support)
            ->post(route('admin.plans.store'), [
                'app_id' => $app->id,
                'name' => 'Blocked Plan',
                'code' => 'BLOCKED',
                'duration_type' => LicenseDurationType::TRIAL->value,
                'duration_days' => 14,
                'default_max_devices' => 1,
                'is_active' => true,
            ])
            ->assertForbidden();

        $this->actingAs($support)
            ->patch(route('admin.plans.update', $plan), [
                'app_id' => $app->id,
                'name' => $plan->name,
                'code' => $plan->code,
                'duration_type' => $plan->duration_type->value,
                'duration_days' => $plan->duration_days,
                'default_max_devices' => $plan->default_max_devices,
                'is_active' => $plan->is_active,
            ])
            ->assertForbidden();
    }
}

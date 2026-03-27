<?php

namespace Tests\Feature\Admin;

use App\Domain\Licensing\LicenseActivationStatus;
use App\Models\App;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\LicensePlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ActivationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_support_users_can_filter_and_view_activation_records(): void
    {
        $this->withoutVite();

        $support = User::factory()->support()->create();
        $desktopApp = App::factory()->create(['name' => 'Desktop']);
        $mobileApp = App::factory()->create(['name' => 'Mobile']);
        $desktopPlan = LicensePlan::factory()->create(['app_id' => $desktopApp->id]);
        $mobilePlan = LicensePlan::factory()->create(['app_id' => $mobileApp->id]);
        $desktopLicense = License::factory()->create([
            'app_id' => $desktopApp->id,
            'plan_id' => $desktopPlan->id,
            'license_key' => 'XARGO-ACTIVE-0001',
        ]);
        $mobileLicense = License::factory()->create([
            'app_id' => $mobileApp->id,
            'plan_id' => $mobilePlan->id,
            'license_key' => 'XARGO-OTHER-0002',
        ]);

        $visibleActivation = LicenseActivation::factory()->create([
            'license_id' => $desktopLicense->id,
            'machine_id' => 'machine-visible',
            'installation_id' => 'installation-visible',
            'status' => LicenseActivationStatus::ACTIVE,
            'last_reason_code' => 'device_mismatch',
        ]);

        LicenseActivation::factory()->create([
            'license_id' => $mobileLicense->id,
            'machine_id' => 'machine-hidden',
            'installation_id' => 'installation-hidden',
            'status' => LicenseActivationStatus::BLOCKED,
        ]);

        $this->actingAs($support)
            ->get(route('admin.activations.index', [
                'machine_id' => 'visible',
                'installation_id' => 'visible',
                'license_key' => 'ACTIVE',
                'app_id' => $desktopApp->id,
                'status' => LicenseActivationStatus::ACTIVE->value,
            ]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Activations/Index')
                ->has('activations', 1)
                ->where('activations.0.id', $visibleActivation->id)
            );

        $this->actingAs($support)
            ->get(route('admin.activations.show', $visibleActivation->id))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Activations/Show')
                ->where('managedActivation.id', $visibleActivation->id)
                ->where('managedActivation.machineId', 'machine-visible')
                ->where('managedActivation.lastReasonCode', 'device_mismatch')
                ->where('can.rebind', false)
            );
    }

    public function test_super_admin_can_view_activation_detail_with_rebind_access(): void
    {
        $this->withoutVite();

        $superAdmin = User::factory()->superAdmin()->create();
        $activation = LicenseActivation::factory()->create([
            'status' => LicenseActivationStatus::STALE,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('admin.activations.show', $activation->id))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Activations/Show')
                ->where('managedActivation.id', $activation->id)
                ->where('managedActivation.status', LicenseActivationStatus::STALE->value)
                ->where('can.rebind', true)
            );
    }
}

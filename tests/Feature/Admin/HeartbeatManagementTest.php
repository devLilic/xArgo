<?php

namespace Tests\Feature\Admin;

use App\Domain\Licensing\LicenseActivationStatus;
use App\Models\App;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\LicenseHeartbeat;
use App\Models\LicensePlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class HeartbeatManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_support_users_can_filter_recent_heartbeat_records(): void
    {
        $this->withoutVite();

        $support = User::factory()->support()->create();
        $desktopApp = App::factory()->create(['name' => 'Desktop', 'app_id' => 'xargo.desktop']);
        $mobileApp = App::factory()->create(['name' => 'Mobile', 'app_id' => 'xargo.mobile']);
        $desktopPlan = LicensePlan::factory()->create(['app_id' => $desktopApp->id]);
        $mobilePlan = LicensePlan::factory()->create(['app_id' => $mobileApp->id]);
        $desktopLicense = License::factory()->create([
            'app_id' => $desktopApp->id,
            'plan_id' => $desktopPlan->id,
            'license_key' => 'XARGO-HB-0001',
        ]);
        $mobileLicense = License::factory()->create([
            'app_id' => $mobileApp->id,
            'plan_id' => $mobilePlan->id,
            'license_key' => 'XARGO-HB-0002',
        ]);

        $visibleActivation = LicenseActivation::factory()->create([
            'license_id' => $desktopLicense->id,
            'activation_id' => 'activation-visible',
            'machine_id' => 'machine-visible',
            'installation_id' => 'installation-visible',
            'status' => LicenseActivationStatus::ACTIVE,
        ]);

        $hiddenActivation = LicenseActivation::factory()->create([
            'license_id' => $mobileLicense->id,
            'activation_id' => 'activation-hidden',
            'machine_id' => 'machine-hidden',
            'installation_id' => 'installation-hidden',
            'status' => LicenseActivationStatus::BLOCKED,
        ]);

        $visibleHeartbeat = LicenseHeartbeat::factory()->create([
            'license_activation_id' => $visibleActivation->id,
            'app_version' => '2.8.0',
            'reason_code' => 'device_mismatch',
        ]);

        LicenseHeartbeat::factory()->create([
            'license_activation_id' => $hiddenActivation->id,
            'app_version' => '1.4.0',
            'reason_code' => 'ok',
        ]);

        $this->actingAs($support)
            ->get(route('admin.heartbeats.index', [
                'app_id' => 'xargo.desktop',
                'license_key' => 'HB-0001',
                'machine_id' => 'visible',
                'installation_id' => 'visible',
                'activation_id' => 'activation-visible',
            ]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Heartbeats/Index')
                ->has('heartbeats', 1)
                ->where('heartbeats.0.id', $visibleHeartbeat->id)
                ->where('heartbeats.0.license.licenseKey', 'XARGO-HB-0001')
                ->where('heartbeats.0.activation.machineId', 'machine-visible')
                ->where('heartbeats.0.activation.installationId', 'installation-visible')
                ->where('heartbeats.0.activation.activationId', 'activation-visible')
                ->where('heartbeats.0.reasonCode', 'device_mismatch')
            );
    }

    public function test_guests_are_redirected_from_heartbeat_admin_page(): void
    {
        $this->get(route('admin.heartbeats.index'))
            ->assertRedirect(route('login'));
    }
}

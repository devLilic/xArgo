<?php

namespace Tests\Feature\Admin;

use App\Domain\Licensing\LicenseActivationStatus;
use App\Domain\Licensing\LicenseStatus;
use App\Models\App;
use App\Models\AuditLog;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\LicensePlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class DashboardOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_exposes_operational_summaries_and_recent_feeds(): void
    {
        $this->withoutVite();
        Carbon::setTestNow('2026-03-27 12:00:00');

        try {
            $user = User::factory()->support()->create();
            $app = App::factory()->create(['app_id' => 'xargo.desktop']);
            $plan = LicensePlan::factory()->create(['app_id' => $app->id]);

            $activeLicense = License::factory()->create([
                'app_id' => $app->id,
                'plan_id' => $plan->id,
                'license_key' => 'XARGO-DASH-0001',
                'status' => LicenseStatus::ACTIVE,
                'expires_at' => now()->addDays(3),
            ]);

            License::factory()->create([
                'app_id' => $app->id,
                'plan_id' => $plan->id,
                'status' => LicenseStatus::ACTIVE,
                'expires_at' => now()->addDays(20),
            ]);

            License::factory()->create([
                'app_id' => $app->id,
                'plan_id' => $plan->id,
                'status' => LicenseStatus::REVOKED,
            ]);

            $mismatchActivation = LicenseActivation::factory()->create([
                'license_id' => $activeLicense->id,
                'activation_id' => 'activation-mismatch',
                'machine_id' => 'machine-mismatch',
                'installation_id' => 'installation-mismatch',
                'status' => LicenseActivationStatus::ACTIVE,
                'last_reason_code' => 'device_mismatch',
                'updated_at' => now()->subHours(2),
            ]);

            LicenseActivation::factory()->create([
                'license_id' => $activeLicense->id,
                'status' => LicenseActivationStatus::STALE,
            ]);

            LicenseActivation::factory()->create([
                'license_id' => $activeLicense->id,
                'status' => LicenseActivationStatus::INACTIVE,
            ]);

            AuditLog::query()->create([
                'user_id' => $user->id,
                'action' => 'admin.license.activation.rebound',
                'entity_type' => 'license_activation',
                'entity_id' => $mismatchActivation->id,
                'meta_json' => [
                    'license_id' => $activeLicense->id,
                    'license_key' => $activeLicense->license_key,
                    'after' => [
                        'machine_id' => 'machine-rebound',
                        'installation_id' => 'installation-rebound',
                    ],
                ],
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ]);

            $this->actingAs($user)
                ->get(route('admin.dashboard'))
                ->assertOk()
                ->assertInertia(fn (AssertableInertia $page) => $page
                    ->component('Admin/Dashboard')
                    ->where('operationalSummary.totalActiveLicenses', 2)
                    ->where('operationalSummary.expiringSoonLicenses', 1)
                    ->where('operationalSummary.recentDeviceMismatches', 1)
                    ->where('operationalSummary.recentRebinds', 1)
                    ->where('operationalSummary.staleOrInactiveActivations', 2)
                    ->where('recentMismatchFeed.0.activationId', 'activation-mismatch')
                    ->where('recentMismatchFeed.0.licenseKey', 'XARGO-DASH-0001')
                    ->where('recentRebindFeed.0.licenseKey', 'XARGO-DASH-0001')
                    ->where('recentRebindFeed.0.nextMachineId', 'machine-rebound')
                );
        } finally {
            Carbon::setTestNow();
        }
    }
}

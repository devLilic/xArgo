<?php

namespace Tests\Feature\Mail;

use App\Actions\Licensing\ManualRebindLicenseActivationAction;
use App\Domain\Licensing\LicenseActivationStatus;
use App\Domain\Licensing\LicenseStatus;
use App\Jobs\SendDeviceMismatchAlertMailJob;
use App\Jobs\SendRebindConfirmedMailJob;
use App\Jobs\SendRebindRequestedMailJob;
use App\Mail\LicenseExpiryWarningMail;
use App\Mail\TrialEndingWarningMail;
use App\Models\App;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\LicensePlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificationTriggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_device_mismatch_alert_job_is_dispatched_after_response_when_enabled(): void
    {
        Bus::fake();

        config()->set('licensing.notifications.device_mismatch_alerts_enabled', true);

        $app = App::factory()->create(['app_id' => 'xargo.desktop']);
        $plan = LicensePlan::factory()->create(['app_id' => $app->id]);
        $license = License::factory()->create([
            'app_id' => $app->id,
            'plan_id' => $plan->id,
            'license_key' => 'XARGO-NOTIFY-MISMATCH',
            'customer_email' => 'customer@example.com',
            'status' => LicenseStatus::ACTIVE,
        ]);
        $plainToken = str_repeat('m', 80);

        LicenseActivation::factory()->create([
            'license_id' => $license->id,
            'machine_id' => 'machine-primary',
            'installation_id' => 'installation-primary',
            'activation_token_hash' => hash('sha256', $plainToken),
            'status' => LicenseActivationStatus::ACTIVE,
            'last_reason_code' => null,
        ]);

        $this->postJson('/api/v1/licenses/validate', [
            'licenseKey' => 'XARGO-NOTIFY-MISMATCH',
            'activationToken' => $plainToken,
            'appId' => 'xargo.desktop',
            'appVersion' => '2.4.0',
            'machineId' => 'machine-clone',
            'installationId' => 'installation-clone',
        ])->assertOk();

        Bus::assertDispatchedAfterResponse(SendDeviceMismatchAlertMailJob::class);
    }

    public function test_device_mismatch_alert_job_is_not_dispatched_when_disabled(): void
    {
        Bus::fake();

        config()->set('licensing.notifications.device_mismatch_alerts_enabled', false);

        $app = App::factory()->create(['app_id' => 'xargo.desktop']);
        $plan = LicensePlan::factory()->create(['app_id' => $app->id]);
        $license = License::factory()->create([
            'app_id' => $app->id,
            'plan_id' => $plan->id,
            'license_key' => 'XARGO-NOTIFY-MISMATCH-OFF',
            'customer_email' => 'customer@example.com',
            'status' => LicenseStatus::ACTIVE,
        ]);
        $plainToken = str_repeat('n', 80);

        LicenseActivation::factory()->create([
            'license_id' => $license->id,
            'machine_id' => 'machine-primary',
            'installation_id' => 'installation-primary',
            'activation_token_hash' => hash('sha256', $plainToken),
            'status' => LicenseActivationStatus::ACTIVE,
        ]);

        $this->postJson('/api/v1/licenses/heartbeat', [
            'activationId' => LicenseActivation::query()->firstOrFail()->activation_id,
            'activationToken' => $plainToken,
            'appId' => 'xargo.desktop',
            'appVersion' => '2.4.0',
            'machineId' => 'machine-clone',
            'installationId' => 'installation-clone',
        ])->assertOk();

        Bus::assertNotDispatched(SendDeviceMismatchAlertMailJob::class);
    }

    public function test_rebind_request_and_manual_rebind_dispatch_after_response_jobs(): void
    {
        Bus::fake();

        config()->set('licensing.notifications.rebind_notifications_enabled', true);

        $app = App::factory()->create(['app_id' => 'xargo.desktop']);
        $plan = LicensePlan::factory()->create(['app_id' => $app->id]);
        $license = License::factory()->create([
            'app_id' => $app->id,
            'plan_id' => $plan->id,
            'license_key' => 'XARGO-NOTIFY-REBIND',
            'customer_email' => 'customer@example.com',
        ]);
        $plainToken = str_repeat('o', 80);
        $activation = LicenseActivation::factory()->create([
            'license_id' => $license->id,
            'machine_id' => 'machine-primary',
            'installation_id' => 'installation-primary',
            'activation_token_hash' => hash('sha256', $plainToken),
            'status' => LicenseActivationStatus::ACTIVE,
        ]);

        $this->postJson('/api/v1/licenses/rebind/request', [
            'licenseKey' => 'XARGO-NOTIFY-REBIND',
            'activationToken' => $plainToken,
            'appId' => 'xargo.desktop',
            'appVersion' => '2.4.0',
            'machineId' => 'machine-replacement',
            'installationId' => 'installation-replacement',
        ])->assertOk();

        Bus::assertDispatchedAfterResponse(SendRebindRequestedMailJob::class);

        app(ManualRebindLicenseActivationAction::class)->execute($activation, [
            'machine_id' => 'machine-replacement',
            'installation_id' => 'installation-replacement',
            'device_label' => 'Replacement Device',
        ]);

        Bus::assertDispatchedAfterResponse(SendRebindConfirmedMailJob::class);
    }

    public function test_scheduled_notification_command_sends_expiry_and_trial_warning_mails(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-03-27 10:00:00');

        try {
            $app = App::factory()->create(['name' => 'xArgo Desktop']);
            $subscriptionPlan = LicensePlan::factory()->subscription()->create([
                'app_id' => $app->id,
            ]);
            $trialPlan = LicensePlan::factory()->trial()->create([
                'app_id' => $app->id,
            ]);

            License::factory()->create([
                'app_id' => $app->id,
                'plan_id' => $subscriptionPlan->id,
                'license_key' => 'XARGO-EXP-WARN',
                'customer_name' => 'Ada Lovelace',
                'customer_email' => 'ada@example.com',
                'status' => LicenseStatus::ACTIVE,
                'expires_at' => now()->addDays((int) config('licensing.notifications.expiry_warning_days'))->setTime(14, 0),
            ]);

            License::factory()->create([
                'app_id' => $app->id,
                'plan_id' => $trialPlan->id,
                'license_key' => 'XARGO-TRIAL-WARN',
                'customer_name' => 'Grace Hopper',
                'customer_email' => 'grace@example.com',
                'status' => LicenseStatus::ACTIVE,
                'expires_at' => now()->addDays((int) config('licensing.notifications.trial_ending_warning_days'))->setTime(16, 30),
            ]);

            $this->artisan('licensing:send-notifications')
                ->expectsOutput('Sent 1 expiry warning notification(s) and 1 trial ending notification(s).')
                ->assertSuccessful();

            Mail::assertSent(LicenseExpiryWarningMail::class, function (LicenseExpiryWarningMail $mail): bool {
                return $mail->hasTo('ada@example.com')
                    && $mail->licenseKey === 'XARGO-EXP-WARN';
            });

            Mail::assertSent(TrialEndingWarningMail::class, function (TrialEndingWarningMail $mail): bool {
                return $mail->hasTo('grace@example.com')
                    && $mail->licenseKey === 'XARGO-TRIAL-WARN';
            });
        } finally {
            Carbon::setTestNow();
        }
    }
}

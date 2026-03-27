<?php

namespace App\Services\Licensing;

use App\Domain\Licensing\LicenseDurationType;
use App\Jobs\SendDeviceMismatchAlertMailJob;
use App\Jobs\SendInvitationMailJob;
use App\Jobs\SendRebindConfirmedMailJob;
use App\Jobs\SendRebindRequestedMailJob;
use App\Mail\LicenseExpiryWarningMail;
use App\Mail\TrialEndingWarningMail;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\UserInvitation;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;

class LicenseNotificationService
{
    public function queueInvitation(UserInvitation $invitation, string $plainToken): void
    {
        SendInvitationMailJob::dispatchAfterResponse($invitation->id, $plainToken);
    }

    public function queueDeviceMismatchAlert(
        LicenseActivation $activation,
        string $machineId,
        string $installationId,
        string $reasonCode,
        ?CarbonInterface $graceUntil = null,
    ): void {
        if (! config('licensing.notifications.device_mismatch_alerts_enabled')) {
            return;
        }

        $activation->loadMissing('license.app');

        if (blank($activation->license->customer_email)) {
            return;
        }

        SendDeviceMismatchAlertMailJob::dispatchAfterResponse(
            $activation->license->customer_email,
            $activation->license->app->name,
            $activation->license->license_key,
            $activation->activation_id,
            $machineId,
            $installationId,
            $reasonCode,
            $graceUntil?->timezone(config('app.timezone'))->format('M j, Y g:i A T'),
        );
    }

    public function queueRebindRequested(
        LicenseActivation $activation,
        string $requestedMachineId,
        string $requestedInstallationId,
        ?CarbonInterface $graceUntil = null,
    ): void {
        if (! config('licensing.notifications.rebind_notifications_enabled')) {
            return;
        }

        $activation->loadMissing('license.app');

        if (blank($activation->license->customer_email)) {
            return;
        }

        SendRebindRequestedMailJob::dispatchAfterResponse(
            $activation->license->customer_email,
            $activation->license->app->name,
            $activation->license->license_key,
            $activation->activation_id,
            $requestedMachineId,
            $requestedInstallationId,
            $graceUntil?->timezone(config('app.timezone'))->format('M j, Y g:i A T'),
        );
    }

    public function queueRebindConfirmed(LicenseActivation $activation): void
    {
        if (! config('licensing.notifications.rebind_notifications_enabled')) {
            return;
        }

        $activation->loadMissing('license.app');

        if (blank($activation->license->customer_email)) {
            return;
        }

        SendRebindConfirmedMailJob::dispatchAfterResponse(
            $activation->license->customer_email,
            $activation->license->app->name,
            $activation->license->license_key,
            $activation->activation_id,
            $activation->machine_id,
            $activation->installation_id,
        );
    }

    /**
     * @return array{expiryWarnings:int, trialEndingWarnings:int}
     */
    public function sendUpcomingWarnings(?CarbonInterface $referenceTime = null): array
    {
        $referenceTime ??= now();

        $expiryWarnings = $this->sendExpiryWarnings($referenceTime);
        $trialEndingWarnings = $this->sendTrialEndingWarnings($referenceTime);

        return [
            'expiryWarnings' => $expiryWarnings,
            'trialEndingWarnings' => $trialEndingWarnings,
        ];
    }

    private function sendExpiryWarnings(CarbonInterface $referenceTime): int
    {
        $days = (int) config('licensing.notifications.expiry_warning_days');

        if ($days <= 0) {
            return 0;
        }

        $count = 0;

        foreach ($this->dueLicensesQuery($referenceTime, $days)
            ->whereHas('plan', fn (Builder $query) => $query->where('duration_type', '!=', LicenseDurationType::TRIAL->value))
            ->get() as $license) {
            Mail::to($license->customer_email)->send(new LicenseExpiryWarningMail(
                customerName: $license->customer_name ?: $license->customer_email,
                appName: $license->app->name,
                licenseKey: $license->license_key,
                expiresAtText: $license->expires_at
                    ?->timezone(config('app.timezone'))
                    ->format('M j, Y g:i A T') ?? 'Unknown',
            ));

            $count++;
        }

        return $count;
    }

    private function sendTrialEndingWarnings(CarbonInterface $referenceTime): int
    {
        $days = (int) config('licensing.notifications.trial_ending_warning_days');

        if ($days <= 0) {
            return 0;
        }

        $count = 0;

        foreach ($this->dueLicensesQuery($referenceTime, $days)
            ->whereHas('plan', fn (Builder $query) => $query->where('duration_type', LicenseDurationType::TRIAL->value))
            ->get() as $license) {
            Mail::to($license->customer_email)->send(new TrialEndingWarningMail(
                customerName: $license->customer_name ?: $license->customer_email,
                appName: $license->app->name,
                licenseKey: $license->license_key,
                trialEndsAtText: $license->expires_at
                    ?->timezone(config('app.timezone'))
                    ->format('M j, Y g:i A T') ?? 'Unknown',
            ));

            $count++;
        }

        return $count;
    }

    private function dueLicensesQuery(CarbonInterface $referenceTime, int $days): Builder
    {
        $targetDay = $referenceTime->copy()->addDays($days);

        return License::query()
            ->with(['app', 'plan'])
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->whereNotNull('customer_email')
            ->whereBetween('expires_at', [
                $targetDay->copy()->startOfDay(),
                $targetDay->copy()->endOfDay(),
            ]);
    }
}

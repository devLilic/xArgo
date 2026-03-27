<?php

namespace Tests\Feature\Mail;

use App\Mail\DeviceMismatchAlertMail;
use App\Mail\InvitationMail;
use App\Mail\LicenseExpiryWarningMail;
use App\Mail\RebindConfirmedMail;
use App\Mail\RebindRequestedMail;
use App\Mail\TrialEndingWarningMail;
use Tests\TestCase;

class LicensingMailablesTest extends TestCase
{
    public function test_invitation_mail_renders_expected_content(): void
    {
        $mail = new InvitationMail(
            inviteeEmail: 'teammate@example.com',
            acceptUrl: 'https://example.com/invitations/accept',
            expiresAtText: 'Mar 30, 2026 10:00 UTC',
        );

        $mail->assertSeeInHtml('Internal team invitation');
        $mail->assertSeeInHtml('teammate@example.com');
        $mail->assertSeeInHtml('https://example.com/invitations/accept');
    }

    public function test_license_expiry_warning_mail_renders_expected_content(): void
    {
        $mail = new LicenseExpiryWarningMail(
            customerName: 'Ada Lovelace',
            appName: 'xArgo Desktop',
            licenseKey: 'XARGO-EXP-0001',
            expiresAtText: 'Apr 02, 2026 09:00 UTC',
        );

        $mail->assertSeeInHtml('License expiry warning');
        $mail->assertSeeInHtml('XARGO-EXP-0001');
        $mail->assertSeeInHtml('Apr 02, 2026 09:00 UTC');
    }

    public function test_trial_ending_warning_mail_renders_expected_content(): void
    {
        $mail = new TrialEndingWarningMail(
            customerName: 'Grace Hopper',
            appName: 'xArgo Desktop',
            licenseKey: 'XARGO-TRIAL-0001',
            trialEndsAtText: 'Apr 05, 2026 12:00 UTC',
        );

        $mail->assertSeeInHtml('Trial ending warning');
        $mail->assertSeeInHtml('XARGO-TRIAL-0001');
        $mail->assertSeeInHtml('Apr 05, 2026 12:00 UTC');
    }

    public function test_device_mismatch_alert_mail_renders_expected_content(): void
    {
        $mail = new DeviceMismatchAlertMail(
            appName: 'xArgo Desktop',
            licenseKey: 'XARGO-MISMATCH-0001',
            activationId: 'activation-001',
            machineId: 'machine-clone',
            installationId: 'installation-clone',
            reasonCode: 'device_mismatch',
            graceUntilText: 'Apr 01, 2026 08:30 UTC',
        );

        $mail->assertSeeInHtml('Suspicious device mismatch detected');
        $mail->assertSeeInHtml('activation-001');
        $mail->assertSeeInHtml('device_mismatch');
        $mail->assertSeeInHtml('Apr 01, 2026 08:30 UTC');
    }

    public function test_rebind_requested_mail_renders_expected_content(): void
    {
        $mail = new RebindRequestedMail(
            appName: 'xArgo Desktop',
            licenseKey: 'XARGO-REBIND-0001',
            activationId: 'activation-002',
            requestedMachineId: 'machine-new',
            requestedInstallationId: 'installation-new',
            graceUntilText: 'Apr 01, 2026 09:15 UTC',
        );

        $mail->assertSeeInHtml('Manual rebind review requested');
        $mail->assertSeeInHtml('machine-new');
        $mail->assertSeeInHtml('Apr 01, 2026 09:15 UTC');
    }

    public function test_rebind_confirmed_mail_renders_expected_content(): void
    {
        $mail = new RebindConfirmedMail(
            appName: 'xArgo Desktop',
            licenseKey: 'XARGO-REBIND-0002',
            activationId: 'activation-003',
            machineId: 'machine-confirmed',
            installationId: 'installation-confirmed',
        );

        $mail->assertSeeInHtml('Manual rebind completed');
        $mail->assertSeeInHtml('machine-confirmed');
        $mail->assertSeeInHtml('installation-confirmed');
    }
}

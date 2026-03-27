<?php

namespace App\Jobs;

use App\Mail\DeviceMismatchAlertMail;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;

class SendDeviceMismatchAlertMailJob
{
    use Dispatchable;

    public function __construct(
        private readonly string $recipientEmail,
        private readonly string $appName,
        private readonly string $licenseKey,
        private readonly string $activationId,
        private readonly string $machineId,
        private readonly string $installationId,
        private readonly string $reasonCode,
        private readonly ?string $graceUntilText,
    ) {
    }

    public function handle(): void
    {
        Mail::to($this->recipientEmail)->send(new DeviceMismatchAlertMail(
            appName: $this->appName,
            licenseKey: $this->licenseKey,
            activationId: $this->activationId,
            machineId: $this->machineId,
            installationId: $this->installationId,
            reasonCode: $this->reasonCode,
            graceUntilText: $this->graceUntilText,
        ));
    }
}

<?php

namespace App\Jobs;

use App\Mail\RebindRequestedMail;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;

class SendRebindRequestedMailJob
{
    use Dispatchable;

    public function __construct(
        private readonly string $recipientEmail,
        private readonly string $appName,
        private readonly string $licenseKey,
        private readonly string $activationId,
        private readonly string $requestedMachineId,
        private readonly string $requestedInstallationId,
        private readonly ?string $graceUntilText,
    ) {
    }

    public function handle(): void
    {
        Mail::to($this->recipientEmail)->send(new RebindRequestedMail(
            appName: $this->appName,
            licenseKey: $this->licenseKey,
            activationId: $this->activationId,
            requestedMachineId: $this->requestedMachineId,
            requestedInstallationId: $this->requestedInstallationId,
            graceUntilText: $this->graceUntilText,
        ));
    }
}

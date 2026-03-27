<?php

namespace App\Jobs;

use App\Mail\RebindConfirmedMail;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;

class SendRebindConfirmedMailJob
{
    use Dispatchable;

    public function __construct(
        private readonly string $recipientEmail,
        private readonly string $appName,
        private readonly string $licenseKey,
        private readonly string $activationId,
        private readonly string $machineId,
        private readonly string $installationId,
    ) {
    }

    public function handle(): void
    {
        Mail::to($this->recipientEmail)->send(new RebindConfirmedMail(
            appName: $this->appName,
            licenseKey: $this->licenseKey,
            activationId: $this->activationId,
            machineId: $this->machineId,
            installationId: $this->installationId,
        ));
    }
}

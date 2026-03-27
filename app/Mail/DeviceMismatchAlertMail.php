<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeviceMismatchAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $appName,
        public readonly string $licenseKey,
        public readonly string $activationId,
        public readonly string $machineId,
        public readonly string $installationId,
        public readonly string $reasonCode,
        public readonly ?string $graceUntilText = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Device mismatch alert',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.device-mismatch-alert',
        );
    }
}

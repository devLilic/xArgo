<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RebindRequestedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $appName,
        public readonly string $licenseKey,
        public readonly string $activationId,
        public readonly string $requestedMachineId,
        public readonly string $requestedInstallationId,
        public readonly ?string $graceUntilText = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'License rebind request',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.rebind-requested',
        );
    }
}

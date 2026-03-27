<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RebindConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $appName,
        public readonly string $licenseKey,
        public readonly string $activationId,
        public readonly string $machineId,
        public readonly string $installationId,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'License rebind confirmed',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.rebind-confirmed',
        );
    }
}

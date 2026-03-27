<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrialEndingWarningMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $customerName,
        public readonly string $appName,
        public readonly string $licenseKey,
        public readonly string $trialEndsAtText,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Trial ending warning',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.trial-ending-warning',
        );
    }
}

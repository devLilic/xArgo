<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $inviteeEmail,
        public readonly string $acceptUrl,
        public readonly string $expiresAtText,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You have been invited to the admin panel',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.invitation',
        );
    }
}

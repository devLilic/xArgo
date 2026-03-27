<?php

namespace App\Notifications;

use App\Models\UserInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly UserInvitation $invitation,
        private readonly string $plainToken,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You have been invited to the admin panel')
            ->greeting('Internal team invitation')
            ->line('You have been invited to activate your internal admin account.')
            ->line('This invitation expires at '.$this->invitation->expires_at->toDayDateTimeString().'.')
            ->action('Accept invitation', route('invitations.accept', [
                'invitation' => $this->invitation,
                'token' => $this->plainToken,
            ]));
    }
}

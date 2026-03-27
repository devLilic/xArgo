<?php

namespace App\Jobs;

use App\Mail\InvitationMail;
use App\Models\UserInvitation;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;

class SendInvitationMailJob
{
    use Dispatchable;

    public function __construct(
        private readonly int $invitationId,
        private readonly string $plainToken,
    ) {
    }

    public function handle(): void
    {
        $invitation = UserInvitation::query()->find($this->invitationId);

        if ($invitation === null) {
            return;
        }

        Mail::to($invitation->email)->send(new InvitationMail(
            inviteeEmail: $invitation->email,
            acceptUrl: route('invitations.accept', [
                'invitation' => $invitation,
                'token' => $this->plainToken,
            ]),
            expiresAtText: $invitation->expires_at
                ->timezone(config('app.timezone'))
                ->format('M j, Y g:i A T'),
        ));
    }
}

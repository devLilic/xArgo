<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Models\UserInvitation;
use App\Services\Licensing\LicenseNotificationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class CreateUserInvitationAction
{
    public function __construct(
        private readonly LicenseNotificationService $notifications,
    ) {
    }

    public function execute(User $inviter, string $email): UserInvitation
    {
        UserInvitation::query()
            ->where('email', $email)
            ->whereNull('accepted_at')
            ->delete();

        $plainToken = Str::random(64);

        $invitation = UserInvitation::query()->create([
            'email' => $email,
            'token_hash' => hash('sha256', $plainToken),
            'invited_by' => $inviter->id,
            'expires_at' => Carbon::now()->addHours(config('auth.invitations.expire_hours')),
        ]);

        $this->notifications->queueInvitation($invitation, $plainToken);

        return $invitation;
    }
}

<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class AcceptUserInvitationAction
{
    /**
     * @param  array{name:string,password:string}  $attributes
     *
     * @throws AuthorizationException
     */
    public function execute(UserInvitation $invitation, string $token, array $attributes): User
    {
        $this->ensureInvitationIsAccessible($invitation, $token);

        return DB::transaction(function () use ($invitation, $attributes): User {
            $user = User::query()->create([
                'name' => $attributes['name'],
                'email' => $invitation->email,
                'password' => $attributes['password'],
                'email_verified_at' => now(),
            ]);

            $invitation->forceFill([
                'accepted_at' => now(),
                'accepted_user_id' => $user->id,
            ])->save();

            return $user;
        });
    }

    /**
     * @throws AuthorizationException
     */
    public function ensureInvitationIsAccessible(UserInvitation $invitation, string $token): void
    {
        if (! $invitation->matchesToken($token)) {
            throw new AuthorizationException;
        }

        if ($invitation->accepted_at !== null || $invitation->hasExpired()) {
            abort(410);
        }
    }
}

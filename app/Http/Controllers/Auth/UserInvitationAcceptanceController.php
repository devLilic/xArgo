<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\AcceptUserInvitationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AcceptUserInvitationRequest;
use App\Models\UserInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class UserInvitationAcceptanceController extends Controller
{
    public function create(UserInvitation $invitation, string $token, AcceptUserInvitationAction $acceptUserInvitation): Response
    {
        $acceptUserInvitation->ensureInvitationIsAccessible($invitation, $token);

        return Inertia::render('Auth/AcceptInvitation', [
            'invitation' => [
                'id' => $invitation->id,
                'email' => $invitation->email,
                'expiresAt' => $invitation->expires_at->toIso8601String(),
                'token' => $token,
            ],
        ]);
    }

    public function store(UserInvitation $invitation, AcceptUserInvitationRequest $request, AcceptUserInvitationAction $acceptUserInvitation): RedirectResponse
    {
        $user = $acceptUserInvitation->execute(
            $invitation,
            $request->string('token')->toString(),
            $request->safe()->only(['name', 'password']),
        );

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }
}

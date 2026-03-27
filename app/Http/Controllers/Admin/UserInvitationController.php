<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Auth\CreateUserInvitationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserInvitationRequest;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;

class UserInvitationController extends Controller
{
    public function store(
        StoreUserInvitationRequest $request,
        CreateUserInvitationAction $createUserInvitation,
        AuditLogger $auditLogger,
    ): RedirectResponse
    {
        $this->authorize('create', \App\Models\UserInvitation::class);

        $invitation = $createUserInvitation->execute(
            $request->user(),
            $request->string('email')->toString(),
        );

        $auditLogger->write(
            $request->user(),
            'admin.invitation.created',
            'user_invitation',
            $invitation->id,
            [
                'email' => $invitation->email,
                'expires_at' => $invitation->expires_at?->toIso8601String(),
            ],
        );

        return redirect()
            ->route('admin.dashboard')
            ->with('status', 'Invitation sent.');
    }
}

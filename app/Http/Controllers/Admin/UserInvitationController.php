<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Auth\CreateUserInvitationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserInvitationRequest;
use Illuminate\Http\RedirectResponse;

class UserInvitationController extends Controller
{
    public function store(StoreUserInvitationRequest $request, CreateUserInvitationAction $createUserInvitation): RedirectResponse
    {
        $this->authorize('create', \App\Models\UserInvitation::class);

        $createUserInvitation->execute(
            $request->user(),
            $request->string('email')->toString(),
        );

        return redirect()
            ->route('admin.dashboard')
            ->with('status', 'Invitation sent.');
    }
}

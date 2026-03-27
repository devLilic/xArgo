<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Auth\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserActivityRequest;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', User::class);

        return Inertia::render('Admin/Users/Index', [
            'users' => User::query()
                ->orderBy('name')
                ->get()
                ->map(fn (User $user): array => $this->presentUser($user))
                ->all(),
            'can' => [
                'inviteUsers' => request()->user()->can('create', \App\Models\UserInvitation::class),
            ],
            'status' => session('status'),
        ]);
    }

    public function show(User $user): Response
    {
        $this->authorize('view', $user);

        return Inertia::render('Admin/Users/Show', [
            'managedUser' => $this->presentUser($user),
            'availableRoles' => array_map(
                fn (Role $role): array => [
                    'value' => $role->value,
                    'label' => str_replace('_', ' ', $role->value),
                ],
                Role::cases(),
            ),
            'can' => [
                'updateRole' => request()->user()->can('updateRole', $user),
                'toggleActive' => request()->user()->can('toggleActive', $user),
            ],
            'status' => session('status'),
        ]);
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        $this->authorize('updateRole', $user);

        $user->forceFill([
            'role' => Role::from($request->string('role')->toString()),
        ])->save();

        return redirect()
            ->route('admin.users.show', $user)
            ->with('status', 'User role updated.');
    }

    public function updateActivity(UpdateUserActivityRequest $request, User $user): RedirectResponse
    {
        $this->authorize('toggleActive', $user);

        $active = $request->boolean('active');

        $user->forceFill([
            'deactivated_at' => $active ? null : now(),
        ])->save();

        return redirect()
            ->route('admin.users.show', $user)
            ->with('status', $active ? 'User reactivated.' : 'User deactivated.');
    }

    private function presentUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
            'isActive' => $user->deactivated_at === null,
            'deactivatedAt' => $user->deactivated_at?->toIso8601String(),
            'createdAt' => $user->created_at?->toIso8601String(),
        ];
    }
}

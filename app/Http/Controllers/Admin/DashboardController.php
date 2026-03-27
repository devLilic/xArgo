<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\License;
use App\Models\LicensePlan;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $this->authorize('viewAdminPanel', request()->user());

        return Inertia::render('Admin/Dashboard', [
            'appName' => config('app.name'),
            'environment' => app()->environment(),
            'invitationStatus' => session('status'),
            'can' => [
                'inviteUsers' => request()->user()->can('create', \App\Models\UserInvitation::class),
                'viewUsers' => request()->user()->can('viewAny', \App\Models\User::class),
                'viewApps' => request()->user()->can('viewAny', App::class),
                'viewPlans' => request()->user()->can('viewAny', LicensePlan::class),
                'viewLicenses' => request()->user()->can('viewAny', License::class),
            ],
            'user' => request()->user()?->only([
                'name',
                'email',
                'role',
            ]),
        ]);
    }
}

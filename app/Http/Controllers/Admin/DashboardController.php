<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'appName' => config('app.name'),
            'environment' => app()->environment(),
            'user' => request()->user()?->only([
                'name',
                'email',
            ]),
        ]);
    }
}

<?php

namespace App\Providers;

use App\Models\App;
use App\Models\LicensePlan;
use App\Models\User;
use App\Models\UserInvitation;
use App\Policies\AppPolicy;
use App\Policies\LicensePlanPolicy;
use App\Policies\UserInvitationPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(App::class, AppPolicy::class);
        Gate::policy(LicensePlan::class, LicensePlanPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(UserInvitation::class, UserInvitationPolicy::class);

        Vite::prefetch(concurrency: 3);
    }
}

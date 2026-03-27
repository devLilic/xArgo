<?php

namespace App\Providers;

use App\Models\App;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\LicenseHeartbeat;
use App\Models\LicensePlan;
use App\Models\User;
use App\Models\UserInvitation;
use App\Policies\AppPolicy;
use App\Policies\LicensePolicy;
use App\Policies\LicenseActivationPolicy;
use App\Policies\LicenseHeartbeatPolicy;
use App\Policies\LicensePlanPolicy;
use App\Policies\UserInvitationPolicy;
use App\Policies\UserPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        RateLimiter::for('licensing-api', function (Request $request) {
            return Limit::perMinute((int) config('licensing.api.rate_limit_per_minute'))
                ->by(implode('|', array_filter([
                    'licensing-api',
                    $request->ip(),
                    $request->route()?->getName(),
                ])));
        });

        Gate::policy(App::class, AppPolicy::class);
        Gate::policy(License::class, LicensePolicy::class);
        Gate::policy(LicenseActivation::class, LicenseActivationPolicy::class);
        Gate::policy(LicenseHeartbeat::class, LicenseHeartbeatPolicy::class);
        Gate::policy(LicensePlan::class, LicensePlanPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(UserInvitation::class, UserInvitationPolicy::class);

        Vite::prefetch(concurrency: 3);
    }
}

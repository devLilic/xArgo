<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\AuditLog;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\LicenseHeartbeat;
use App\Models\LicensePlan;
use Illuminate\Database\Eloquent\Builder;
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
            'operationalSummary' => [
                'totalActiveLicenses' => License::query()
                    ->where('status', 'active')
                    ->count(),
                'expiringSoonLicenses' => License::query()
                    ->where('status', 'active')
                    ->whereNotNull('expires_at')
                    ->whereBetween('expires_at', [
                        now(),
                        now()->copy()->addDays((int) config('licensing.notifications.expiry_warning_days')),
                    ])
                    ->count(),
                'recentDeviceMismatches' => LicenseActivation::query()
                    ->where('last_reason_code', 'device_mismatch')
                    ->where('updated_at', '>=', now()->subDay())
                    ->count(),
                'recentRebinds' => AuditLog::query()
                    ->where('action', 'admin.license.activation.rebound')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count(),
                'staleOrInactiveActivations' => LicenseActivation::query()
                    ->whereIn('status', ['stale', 'inactive'])
                    ->count(),
            ],
            'recentMismatchFeed' => LicenseActivation::query()
                ->with(['license.app'])
                ->where('last_reason_code', 'device_mismatch')
                ->where('updated_at', '>=', now()->subDay())
                ->latest('updated_at')
                ->limit(5)
                ->get()
                ->map(fn (LicenseActivation $activation): array => [
                    'id' => $activation->id,
                    'activationId' => $activation->activation_id,
                    'licenseId' => $activation->license->id,
                    'licenseKey' => $activation->license->license_key,
                    'appId' => $activation->license->app->app_id,
                    'machineId' => $activation->machine_id,
                    'installationId' => $activation->installation_id,
                    'reasonCode' => $activation->last_reason_code,
                    'seenAt' => $activation->updated_at?->toIso8601String(),
                ])
                ->all(),
            'recentRebindFeed' => AuditLog::query()
                ->with('user')
                ->where('action', 'admin.license.activation.rebound')
                ->latest('created_at')
                ->limit(5)
                ->get()
                ->map(fn (AuditLog $auditLog): array => [
                    'id' => $auditLog->id,
                    'licenseId' => $this->integerMeta($auditLog, 'license_id'),
                    'licenseKey' => $this->stringMeta($auditLog, 'license_key'),
                    'entityId' => $auditLog->entity_id,
                    'actor' => $auditLog->user === null ? 'System' : $auditLog->user->email,
                    'createdAt' => $auditLog->created_at?->toIso8601String(),
                    'nextMachineId' => data_get($auditLog->meta_json, 'after.machine_id'),
                    'nextInstallationId' => data_get($auditLog->meta_json, 'after.installation_id'),
                ])
                ->all(),
            'can' => [
                'inviteUsers' => request()->user()->can('create', \App\Models\UserInvitation::class),
                'viewUsers' => request()->user()->can('viewAny', \App\Models\User::class),
                'viewApps' => request()->user()->can('viewAny', App::class),
                'viewPlans' => request()->user()->can('viewAny', LicensePlan::class),
                'viewLicenses' => request()->user()->can('viewAny', License::class),
                'viewHeartbeats' => request()->user()->can('viewAny', LicenseHeartbeat::class),
                'viewAuditLogs' => request()->user()->can('viewAny', AuditLog::class),
            ],
            'user' => request()->user()?->only([
                'name',
                'email',
                'role',
            ]),
        ]);
    }

    private function integerMeta(AuditLog $auditLog, string $key): ?int
    {
        $value = data_get($auditLog->meta_json, $key);

        return is_numeric($value) ? (int) $value : null;
    }

    private function stringMeta(AuditLog $auditLog, string $key): ?string
    {
        $value = data_get($auditLog->meta_json, $key);

        return is_string($value) && $value !== '' ? $value : null;
    }
}

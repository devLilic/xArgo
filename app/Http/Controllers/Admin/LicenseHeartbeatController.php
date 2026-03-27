<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\LicenseHeartbeat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LicenseHeartbeatController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', LicenseHeartbeat::class);

        return Inertia::render('Admin/Heartbeats/Index', [
            'heartbeats' => $this->filteredHeartbeatQuery($request)
                ->latest('received_at')
                ->latest('id')
                ->get()
                ->map(fn (LicenseHeartbeat $heartbeat): array => $this->presentHeartbeatRecord($heartbeat))
                ->all(),
            'apps' => App::query()
                ->orderBy('name')
                ->get()
                ->map(fn (App $app): array => [
                    'id' => $app->id,
                    'name' => $app->name,
                    'appId' => $app->app_id,
                ])
                ->all(),
            'filters' => [
                'app_id' => $request->filled('app_id') ? $request->integer('app_id') : null,
                'license_key' => $request->string('license_key')->toString(),
                'machine_id' => $request->string('machine_id')->toString(),
                'installation_id' => $request->string('installation_id')->toString(),
                'activation_id' => $request->string('activation_id')->toString(),
            ],
            'status' => session('status'),
        ]);
    }

    private function filteredHeartbeatQuery(Request $request): Builder
    {
        return $this->baseHeartbeatQuery()
            ->when(
                $request->filled('app_id'),
                fn (Builder $query) => $query->whereHas(
                    'activation.license',
                    fn (Builder $licenseQuery) => $licenseQuery->where('app_id', $request->integer('app_id'))
                )
            )
            ->when(
                $request->string('license_key')->toString() !== '',
                fn (Builder $query) => $query->whereHas(
                    'activation.license',
                    fn (Builder $licenseQuery) => $licenseQuery->where(
                        'license_key',
                        'like',
                        '%'.$request->string('license_key')->toString().'%'
                    )
                )
            )
            ->when(
                $request->string('machine_id')->toString() !== '',
                fn (Builder $query) => $query->whereHas(
                    'activation',
                    fn (Builder $activationQuery) => $activationQuery->where(
                        'machine_id',
                        'like',
                        '%'.$request->string('machine_id')->toString().'%'
                    )
                )
            )
            ->when(
                $request->string('installation_id')->toString() !== '',
                fn (Builder $query) => $query->whereHas(
                    'activation',
                    fn (Builder $activationQuery) => $activationQuery->where(
                        'installation_id',
                        'like',
                        '%'.$request->string('installation_id')->toString().'%'
                    )
                )
            )
            ->when(
                $request->string('activation_id')->toString() !== '',
                fn (Builder $query) => $query->whereHas(
                    'activation',
                    fn (Builder $activationQuery) => $activationQuery->where(
                        'activation_id',
                        'like',
                        '%'.$request->string('activation_id')->toString().'%'
                    )
                )
            );
    }

    private function baseHeartbeatQuery(): Builder
    {
        return LicenseHeartbeat::query()
            ->with(['activation.license.app']);
    }

    private function presentHeartbeatRecord(LicenseHeartbeat $heartbeat): array
    {
        return [
            'id' => $heartbeat->id,
            'appVersion' => $heartbeat->app_version,
            'receivedAt' => $heartbeat->received_at?->toIso8601String(),
            'ipAddress' => $heartbeat->ip_address,
            'reasonCode' => $heartbeat->reason_code,
            'activation' => [
                'id' => $heartbeat->activation->id,
                'activationId' => $heartbeat->activation->activation_id,
                'machineId' => $heartbeat->activation->machine_id,
                'installationId' => $heartbeat->activation->installation_id,
                'status' => $heartbeat->activation->status->value,
            ],
            'license' => [
                'id' => $heartbeat->activation->license->id,
                'licenseKey' => $heartbeat->activation->license->license_key,
                'publicKey' => $heartbeat->activation->license->public_key,
            ],
            'app' => [
                'id' => $heartbeat->activation->license->app->id,
                'name' => $heartbeat->activation->license->app->name,
                'appId' => $heartbeat->activation->license->app->app_id,
            ],
        ];
    }
}

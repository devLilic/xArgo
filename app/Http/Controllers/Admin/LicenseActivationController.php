<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Licensing\ManualRebindLicenseActivationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RebindLicenseActivationRequest;
use App\Models\App;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Services\Audit\AuditLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LicenseActivationController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', LicenseActivation::class);

        return Inertia::render('Admin/Activations/Index', [
            'activations' => $this->filteredActivationQuery($request)
                ->latest('id')
                ->get()
                ->map(fn (LicenseActivation $activation): array => $this->presentActivationRecord($activation))
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
            'statuses' => array_map(
                fn ($status): array => [
                    'value' => $status->value,
                    'label' => ucfirst($status->value),
                ],
                \App\Domain\Licensing\LicenseActivationStatus::cases(),
            ),
            'filters' => [
                'machine_id' => $request->string('machine_id')->toString(),
                'installation_id' => $request->string('installation_id')->toString(),
                'license_key' => $request->string('license_key')->toString(),
                'app_id' => $request->filled('app_id') ? $request->integer('app_id') : null,
                'status' => $request->string('status')->toString(),
            ],
            'status' => session('status'),
        ]);
    }

    public function show(Request $request, int $activation): Response
    {
        $managedActivation = $this->baseActivationQuery()->findOrFail($activation);
        $this->authorize('view', $managedActivation);

        return Inertia::render('Admin/Activations/Show', [
            'managedActivation' => $this->presentActivationRecord($managedActivation),
            'can' => [
                'rebind' => $request->user()->can('rebind', $managedActivation),
            ],
            'status' => session('status'),
        ]);
    }

    public function edit(Request $request, int $license, int $activation): Response
    {
        [$managedLicense, $managedActivation] = $this->resolveScopedModels($license, $activation);

        $this->authorize('view', $managedActivation);

        return Inertia::render('Admin/Licenses/RebindActivation', [
            'managedLicense' => $this->presentLicense($managedLicense),
            'managedActivation' => $this->presentActivation($managedActivation),
            'can' => [
                'rebind' => $request->user()->can('rebind', $managedActivation),
            ],
            'status' => session('status'),
        ]);
    }

    public function update(
        RebindLicenseActivationRequest $request,
        int $license,
        int $activation,
        ManualRebindLicenseActivationAction $manualRebind,
        AuditLogger $auditLogger,
    ): RedirectResponse {
        [$managedLicense, $managedActivation] = $this->resolveScopedModels($license, $activation);

        $this->authorize('rebind', $managedActivation);

        $before = $this->activationAuditSnapshot($managedActivation);
        $reboundActivation = $manualRebind->execute($managedActivation, $request->validated());

        $auditLogger->write(
            $request->user(),
            'admin.license.activation.rebound',
            'license_activation',
            $reboundActivation->id,
            [
                'license_id' => $managedLicense->id,
                'license_key' => $managedLicense->license_key,
                'before' => $before,
                'after' => $this->activationAuditSnapshot($reboundActivation),
            ],
        );

        return redirect()
            ->route('admin.licenses.show', $managedLicense->id)
            ->with('status', 'Activation rebound manually.');
    }

    /**
     * @return array{License, LicenseActivation}
     */
    private function resolveScopedModels(int $licenseId, int $activationId): array
    {
        $managedLicense = License::query()
            ->with(['app', 'plan', 'activations'])
            ->findOrFail($licenseId);

        $managedActivation = $managedLicense->activations()
            ->findOrFail($activationId);

        return [$managedLicense, $managedActivation];
    }

    private function presentLicense(License $license): array
    {
        return [
            'id' => $license->id,
            'licenseKey' => $license->license_key,
            'publicKey' => $license->public_key,
            'app' => [
                'name' => $license->app->name,
                'appId' => $license->app->app_id,
            ],
            'plan' => [
                'name' => $license->plan->name,
                'code' => $license->plan->code,
            ],
        ];
    }

    private function presentActivation(LicenseActivation $activation): array
    {
        return [
            'id' => $activation->id,
            'activationId' => $activation->activation_id,
            'machineId' => $activation->machine_id,
            'installationId' => $activation->installation_id,
            'deviceLabel' => $activation->device_label,
            'status' => $activation->status->value,
            'firstSeenAt' => $activation->first_seen_at?->toIso8601String(),
            'lastSeenAt' => $activation->last_seen_at?->toIso8601String(),
            'graceUntil' => $activation->grace_until?->toIso8601String(),
            'lastReasonCode' => $activation->last_reason_code,
        ];
    }

    private function presentActivationRecord(LicenseActivation $activation): array
    {
        return array_merge($this->presentActivation($activation), [
            'license' => [
                'id' => $activation->license->id,
                'licenseKey' => $activation->license->license_key,
                'publicKey' => $activation->license->public_key,
            ],
            'app' => [
                'id' => $activation->license->app->id,
                'name' => $activation->license->app->name,
                'appId' => $activation->license->app->app_id,
            ],
        ]);
    }

    private function activationAuditSnapshot(LicenseActivation $activation): array
    {
        return [
            'activation_id' => $activation->activation_id,
            'machine_id' => $activation->machine_id,
            'installation_id' => $activation->installation_id,
            'device_label' => $activation->device_label,
            'status' => $activation->status->value,
            'first_seen_at' => $activation->first_seen_at?->toIso8601String(),
            'last_seen_at' => $activation->last_seen_at?->toIso8601String(),
            'grace_until' => $activation->grace_until?->toIso8601String(),
            'last_reason_code' => $activation->last_reason_code,
        ];
    }

    private function filteredActivationQuery(Request $request): Builder
    {
        return $this->baseActivationQuery()
            ->when($request->string('machine_id')->toString() !== '', fn (Builder $query) => $query->where('machine_id', 'like', '%'.$request->string('machine_id')->toString().'%'))
            ->when($request->string('installation_id')->toString() !== '', fn (Builder $query) => $query->where('installation_id', 'like', '%'.$request->string('installation_id')->toString().'%'))
            ->when($request->string('license_key')->toString() !== '', fn (Builder $query) => $query->whereHas('license', fn (Builder $licenseQuery) => $licenseQuery->where('license_key', 'like', '%'.$request->string('license_key')->toString().'%')))
            ->when($request->filled('app_id'), fn (Builder $query) => $query->whereHas('license', fn (Builder $licenseQuery) => $licenseQuery->where('app_id', $request->integer('app_id'))))
            ->when($request->string('status')->toString() !== '', fn (Builder $query) => $query->where('status', $request->string('status')->toString()));
    }

    private function baseActivationQuery(): Builder
    {
        return LicenseActivation::query()
            ->with(['license.app']);
    }
}

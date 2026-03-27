<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Licensing\CreateLicenseAction;
use App\Actions\Licensing\TransitionLicenseStatusAction;
use App\Actions\Licensing\UpdateLicenseAction;
use App\Domain\Licensing\LicenseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLicenseRequest;
use App\Http\Requests\Admin\UpdateLicenseRequest;
use App\Http\Requests\Admin\UpdateLicenseStatusRequest;
use App\Models\App;
use App\Models\License;
use App\Models\LicensePlan;
use App\Services\Audit\AuditLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LicenseController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', License::class);

        return Inertia::render('Admin/Licenses/Index', [
            'licenses' => $this->filteredLicenseQuery($request)
                ->latest('id')
                ->get()
                ->map(fn (License $license): array => $this->presentLicense($license))
                ->all(),
            'apps' => $this->presentApps(),
            'plans' => $this->presentPlans(),
            'statuses' => $this->presentStatuses(),
            'filters' => [
                'license_key' => $request->string('license_key')->toString(),
                'customer_email' => $request->string('customer_email')->toString(),
                'app_id' => $request->string('app_id')->toString(),
                'status' => $request->string('status')->toString(),
            ],
            'defaults' => [
                'maxDevices' => (int) config('licensing.devices.default_max_devices'),
                'graceHours' => (int) ceil(config('licensing.device_mismatch.grace_period_seconds') / 3600),
            ],
            'can' => [
                'create' => $request->user()->can('create', License::class),
                'export' => $request->user()->can('export', License::class),
            ],
            'status' => session('status'),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', License::class);

        $fileName = 'licenses-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($request): void {
            $handle = fopen('php://output', 'wb');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, [
                'license_key',
                'public_key',
                'customer_name',
                'customer_email',
                'app_name',
                'app_id',
                'plan_name',
                'plan_code',
                'status',
                'max_devices',
                'expires_at',
                'grace_hours',
                'last_validated_at',
                'archived_at',
                'created_at',
            ]);

            $this->filteredLicenseQuery($request)
                ->latest('id')
                ->chunk(200, function ($licenses) use ($handle): void {
                    foreach ($licenses as $license) {
                        fputcsv($handle, [
                            $license->license_key,
                            $license->public_key,
                            $license->customer_name,
                            $license->customer_email,
                            $license->app->name,
                            $license->app->app_id,
                            $license->plan->name,
                            $license->plan->code,
                            $license->status->value,
                            $license->max_devices,
                            $license->expires_at?->toIso8601String(),
                            $license->grace_hours,
                            $license->last_validated_at?->toIso8601String(),
                            $license->deleted_at?->toIso8601String(),
                            $license->created_at?->toIso8601String(),
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function store(
        StoreLicenseRequest $request,
        CreateLicenseAction $createLicense,
        AuditLogger $auditLogger,
    ): RedirectResponse
    {
        $this->authorize('create', License::class);

        $plan = LicensePlan::query()->findOrFail($request->integer('plan_id'));

        $license = $createLicense->execute($plan, $request->validated());
        $this->writeLicenseAuditLog($auditLogger, $request, 'admin.license.created', $license, [
            'created_status' => $license->status->value,
        ]);

        return redirect()
            ->route('admin.licenses.show', $license->id)
            ->with('status', 'License created.');
    }

    public function show(Request $request, int $license): Response
    {
        $license = $this->findManagedLicense($license);
        $this->authorize('view', $license);

        return Inertia::render('Admin/Licenses/Show', [
            'managedLicense' => $this->presentLicense($license),
            'can' => $this->presentLicensePermissions($request, $license),
            'status' => session('status'),
        ]);
    }

    public function edit(Request $request, int $license): Response
    {
        $license = $this->findManagedLicense($license);
        $this->authorize('view', $license);

        return Inertia::render('Admin/Licenses/Edit', [
            'managedLicense' => $this->presentLicense($license),
            'apps' => $this->presentApps(),
            'plans' => $this->presentPlans(),
            'can' => $this->presentLicensePermissions($request, $license),
            'status' => session('status'),
        ]);
    }

    public function update(
        UpdateLicenseRequest $request,
        int $license,
        UpdateLicenseAction $updateLicense,
        AuditLogger $auditLogger,
    ): RedirectResponse
    {
        $license = $this->findManagedLicense($license);
        $this->authorize('update', $license);

        $before = $this->licenseAuditSnapshot($license);
        $updateLicense->execute($license, $request->validated());
        $updatedLicense = $license->fresh(['app', 'plan']);
        $this->writeLicenseAuditLog($auditLogger, $request, 'admin.license.updated', $updatedLicense, [
            'before' => $before,
            'after' => $this->licenseAuditSnapshot($updatedLicense),
        ]);

        return redirect()
            ->route('admin.licenses.edit', $license->id)
            ->with('status', 'License updated.');
    }

    public function updateStatus(
        UpdateLicenseStatusRequest $request,
        int $license,
        TransitionLicenseStatusAction $transitionStatus,
        AuditLogger $auditLogger,
    ): RedirectResponse
    {
        $license = $this->findManagedLicense($license);
        $this->authorize('changeStatus', $license);

        $previousStatus = $license->status->value;
        $updatedLicense = $transitionStatus->execute($license, $request->string('status_action')->toString());
        $event = match ($request->string('status_action')->toString()) {
            'suspend' => 'admin.license.suspended',
            'revoke' => 'admin.license.revoked',
            'reactivate' => 'admin.license.reactivated',
        };
        $this->writeLicenseAuditLog($auditLogger, $request, $event, $updatedLicense, [
            'before_status' => $previousStatus,
            'after_status' => $updatedLicense->status->value,
        ]);

        return redirect()
            ->route('admin.licenses.show', $license->id)
            ->with('status', 'License status updated.');
    }

    public function destroy(Request $request, int $license): RedirectResponse
    {
        $license = $this->findManagedLicense($license);
        $this->authorize('delete', $license);

        $license->delete();

        return redirect()
            ->route('admin.licenses.index')
            ->with('status', 'License archived.');
    }

    public function restore(Request $request, int $license, AuditLogger $auditLogger): RedirectResponse
    {
        $license = $this->findManagedLicense($license);
        $this->authorize('restore', $license);

        $license->restore();
        $restoredLicense = $license->fresh(['app', 'plan']);
        $this->writeLicenseAuditLog($auditLogger, $request, 'admin.license.restored', $restoredLicense, [
            'restored_from_archived' => true,
        ]);

        return redirect()
            ->route('admin.licenses.show', $license->id)
            ->with('status', 'License restored.');
    }

    private function findManagedLicense(int $licenseId): License
    {
        return $this->baseLicenseQuery()
            ->findOrFail($licenseId);
    }

    private function filteredLicenseQuery(Request $request): Builder
    {
        return $this->baseLicenseQuery()
            ->when($request->string('license_key')->toString() !== '', fn (Builder $query) => $query->where('license_key', 'like', '%'.$request->string('license_key')->toString().'%'))
            ->when($request->string('customer_email')->toString() !== '', fn (Builder $query) => $query->where('customer_email', 'like', '%'.$request->string('customer_email')->toString().'%'))
            ->when(
                $request->string('app_id')->toString() !== '',
                fn (Builder $query) => $query->whereHas(
                    'app',
                    fn (Builder $appQuery) => $appQuery->where('app_id', 'like', '%'.$request->string('app_id')->toString().'%')
                )
            )
            ->when($request->string('status')->toString() !== '', fn (Builder $query) => $query->where('status', $request->string('status')->toString()));
    }

    private function baseLicenseQuery(): Builder
    {
        return License::query()
            ->with(['app', 'plan', 'activations'])
            ->withTrashed();
    }

    private function presentLicense(License $license): array
    {
        return [
            'id' => $license->id,
            'publicKey' => $license->public_key,
            'licenseKey' => $license->license_key,
            'customerName' => $license->customer_name,
            'customerEmail' => $license->customer_email,
            'status' => $license->status->value,
            'maxDevices' => $license->max_devices,
            'expiresAt' => $license->expires_at?->toIso8601String(),
            'graceHours' => $license->grace_hours,
            'notes' => $license->notes,
            'lastValidatedAt' => $license->last_validated_at?->toIso8601String(),
            'deletedAt' => $license->deleted_at?->toIso8601String(),
            'createdAt' => $license->created_at?->toIso8601String(),
            'app' => [
                'id' => $license->app->id,
                'name' => $license->app->name,
                'appId' => $license->app->app_id,
            ],
            'plan' => [
                'id' => $license->plan->id,
                'name' => $license->plan->name,
                'code' => $license->plan->code,
            ],
            'activations' => $license->activations
                ->map(fn ($activation): array => [
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
                ])
                ->values()
                ->all(),
        ];
    }

    private function presentApps(): array
    {
        return App::query()
            ->orderBy('name')
            ->get()
            ->map(fn (App $app): array => [
                'id' => $app->id,
                'name' => $app->name,
                'appId' => $app->app_id,
            ])
            ->all();
    }

    private function presentPlans(): array
    {
        return LicensePlan::query()
            ->with('app')
            ->orderBy('name')
            ->get()
            ->map(fn (LicensePlan $plan): array => [
                'id' => $plan->id,
                'appId' => $plan->app_id,
                'name' => $plan->name,
                'code' => $plan->code,
                'defaultMaxDevices' => $plan->default_max_devices,
            ])
            ->all();
    }

    private function presentStatuses(): array
    {
        return array_map(
            fn (LicenseStatus $status): array => [
                'value' => $status->value,
                'label' => ucfirst($status->value),
            ],
            LicenseStatus::cases(),
        );
    }

    private function presentLicensePermissions(Request $request, License $license): array
    {
        return [
            'update' => $request->user()->can('update', $license),
            'changeStatus' => $request->user()->can('changeStatus', $license),
            'delete' => $request->user()->can('delete', $license) && ! $license->trashed(),
            'restore' => $request->user()->can('restore', $license) && $license->trashed(),
            'rebindActivation' => $request->user()->can('update', $license),
        ];
    }

    private function writeLicenseAuditLog(
        AuditLogger $auditLogger,
        Request $request,
        string $event,
        License $license,
        array $metadata = [],
    ): void {
        $auditLogger->write(
            $request->user(),
            $event,
            'license',
            $license->id,
            array_merge($this->licenseAuditSnapshot($license), $metadata),
        );
    }

    private function licenseAuditSnapshot(License $license): array
    {
        return [
            'license_key' => $license->license_key,
            'public_key' => $license->public_key,
            'customer_email' => $license->customer_email,
            'app_id' => $license->app_id,
            'plan_id' => $license->plan_id,
            'status' => $license->status->value,
            'max_devices' => $license->max_devices,
            'expires_at' => $license->expires_at?->toIso8601String(),
            'grace_hours' => $license->grace_hours,
            'archived_at' => $license->deleted_at?->toIso8601String(),
        ];
    }
}

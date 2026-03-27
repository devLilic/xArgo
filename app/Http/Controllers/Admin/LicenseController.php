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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LicenseController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', License::class);

        return Inertia::render('Admin/Licenses/Index', [
            'licenses' => License::query()
                ->with(['app', 'plan'])
                ->withTrashed()
                ->when($request->string('license_key')->toString() !== '', fn (Builder $query) => $query->where('license_key', 'like', '%'.$request->string('license_key')->toString().'%'))
                ->when($request->string('customer_email')->toString() !== '', fn (Builder $query) => $query->where('customer_email', 'like', '%'.$request->string('customer_email')->toString().'%'))
                ->when($request->filled('app_id'), fn (Builder $query) => $query->where('app_id', $request->integer('app_id')))
                ->when($request->string('status')->toString() !== '', fn (Builder $query) => $query->where('status', $request->string('status')->toString()))
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
                'app_id' => $request->filled('app_id') ? $request->integer('app_id') : null,
                'status' => $request->string('status')->toString(),
            ],
            'defaults' => [
                'maxDevices' => (int) config('licensing.devices.default_max_devices'),
                'graceHours' => (int) ceil(config('licensing.device_mismatch.grace_period_seconds') / 3600),
            ],
            'can' => [
                'create' => $request->user()->can('create', License::class),
            ],
            'status' => session('status'),
        ]);
    }

    public function store(StoreLicenseRequest $request, CreateLicenseAction $createLicense): RedirectResponse
    {
        $this->authorize('create', License::class);

        $plan = LicensePlan::query()->findOrFail($request->integer('plan_id'));

        $license = $createLicense->execute($plan, $request->validated());

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

    public function update(UpdateLicenseRequest $request, int $license, UpdateLicenseAction $updateLicense): RedirectResponse
    {
        $license = $this->findManagedLicense($license);
        $this->authorize('update', $license);

        $updateLicense->execute($license, $request->validated());

        return redirect()
            ->route('admin.licenses.edit', $license->id)
            ->with('status', 'License updated.');
    }

    public function updateStatus(UpdateLicenseStatusRequest $request, int $license, TransitionLicenseStatusAction $transitionStatus): RedirectResponse
    {
        $license = $this->findManagedLicense($license);
        $this->authorize('changeStatus', $license);

        $transitionStatus->execute($license, $request->string('status_action')->toString());

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

    public function restore(Request $request, int $license): RedirectResponse
    {
        $license = $this->findManagedLicense($license);
        $this->authorize('restore', $license);

        $license->restore();

        return redirect()
            ->route('admin.licenses.show', $license->id)
            ->with('status', 'License restored.');
    }

    private function findManagedLicense(int $licenseId): License
    {
        return License::query()
            ->with(['app', 'plan'])
            ->withTrashed()
            ->findOrFail($licenseId);
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
        ];
    }
}

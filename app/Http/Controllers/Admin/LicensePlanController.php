<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Licensing\LicenseDurationType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLicensePlanRequest;
use App\Http\Requests\Admin\UpdateLicensePlanRequest;
use App\Models\App;
use App\Models\LicensePlan;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LicensePlanController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', LicensePlan::class);

        return Inertia::render('Admin/Plans/Index', [
            'plans' => LicensePlan::query()
                ->with('app')
                ->orderBy('name')
                ->get()
                ->map(fn (LicensePlan $plan): array => $this->presentPlan($plan))
                ->all(),
            'apps' => App::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(fn (App $app): array => [
                    'id' => $app->id,
                    'name' => $app->name,
                    'appId' => $app->app_id,
                ])
                ->all(),
            'durationTypes' => array_map(
                fn (LicenseDurationType $type): array => [
                    'value' => $type->value,
                    'label' => ucfirst($type->value),
                ],
                LicenseDurationType::cases(),
            ),
            'defaults' => [
                'maxDevices' => (int) config('licensing.defaults.max_devices'),
            ],
            'can' => [
                'create' => request()->user()->can('create', LicensePlan::class),
            ],
            'status' => session('status'),
        ]);
    }

    public function store(StoreLicensePlanRequest $request): RedirectResponse
    {
        $this->authorize('create', LicensePlan::class);

        LicensePlan::query()->create($this->validatedPlanAttributes($request->validated()));

        return redirect()
            ->route('admin.plans.index')
            ->with('status', 'Plan created.');
    }

    public function edit(LicensePlan $plan): Response
    {
        $this->authorize('view', $plan);

        return Inertia::render('Admin/Plans/Edit', [
            'managedPlan' => $this->presentPlan($plan->load('app')),
            'apps' => App::query()
                ->orderBy('name')
                ->get()
                ->map(fn (App $app): array => [
                    'id' => $app->id,
                    'name' => $app->name,
                    'appId' => $app->app_id,
                ])
                ->all(),
            'durationTypes' => array_map(
                fn (LicenseDurationType $type): array => [
                    'value' => $type->value,
                    'label' => ucfirst($type->value),
                ],
                LicenseDurationType::cases(),
            ),
            'can' => [
                'update' => request()->user()->can('update', $plan),
            ],
            'status' => session('status'),
        ]);
    }

    public function update(UpdateLicensePlanRequest $request, LicensePlan $plan): RedirectResponse
    {
        $this->authorize('update', $plan);

        $plan->update($this->validatedPlanAttributes($request->validated()));

        return redirect()
            ->route('admin.plans.edit', $plan)
            ->with('status', 'Plan updated.');
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function validatedPlanAttributes(array $validated): array
    {
        if ($validated['duration_type'] === LicenseDurationType::PERMANENT->value) {
            $validated['duration_days'] = null;
        }

        return $validated;
    }

    private function presentPlan(LicensePlan $plan): array
    {
        return [
            'id' => $plan->id,
            'app' => [
                'id' => $plan->app->id,
                'name' => $plan->app->name,
                'appId' => $plan->app->app_id,
            ],
            'name' => $plan->name,
            'code' => $plan->code,
            'durationType' => $plan->duration_type->value,
            'durationDays' => $plan->duration_days,
            'defaultMaxDevices' => $plan->default_max_devices,
            'isActive' => $plan->is_active,
            'createdAt' => $plan->created_at?->toIso8601String(),
        ];
    }
}

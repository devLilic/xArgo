<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAppRequest;
use App\Http\Requests\Admin\UpdateAppRequest;
use App\Models\App;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AppController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', App::class);

        return Inertia::render('Admin/Apps/Index', [
            'apps' => App::query()
                ->orderBy('name')
                ->get()
                ->map(fn (App $app): array => $this->presentApp($app))
                ->all(),
            'can' => [
                'create' => request()->user()->can('create', App::class),
            ],
            'status' => session('status'),
        ]);
    }

    public function store(StoreAppRequest $request): RedirectResponse
    {
        $this->authorize('create', App::class);

        App::query()->create($request->validated());

        return redirect()
            ->route('admin.apps.index')
            ->with('status', 'Application created.');
    }

    public function edit(App $app): Response
    {
        $this->authorize('view', $app);

        return Inertia::render('Admin/Apps/Edit', [
            'managedApp' => $this->presentApp($app),
            'can' => [
                'update' => request()->user()->can('update', $app),
            ],
            'status' => session('status'),
        ]);
    }

    public function update(UpdateAppRequest $request, App $app): RedirectResponse
    {
        $this->authorize('update', $app);

        $app->update($request->validated());

        return redirect()
            ->route('admin.apps.edit', $app)
            ->with('status', 'Application updated.');
    }

    private function presentApp(App $app): array
    {
        return [
            'id' => $app->id,
            'name' => $app->name,
            'slug' => $app->slug,
            'appId' => $app->app_id,
            'isActive' => $app->is_active,
            'createdAt' => $app->created_at?->toIso8601String(),
        ];
    }
}

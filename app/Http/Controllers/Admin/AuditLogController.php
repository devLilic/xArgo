<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', AuditLog::class);

        return Inertia::render('Admin/AuditLogs/Index', [
            'auditLogs' => $this->filteredAuditLogQuery($request)
                ->latest('created_at')
                ->latest('id')
                ->get()
                ->map(fn (AuditLog $auditLog): array => $this->presentAuditLogSummary($auditLog))
                ->all(),
            'actors' => User::query()
                ->orderBy('name')
                ->get()
                ->map(fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ])
                ->all(),
            'filters' => [
                'user_id' => $request->filled('user_id') ? $request->integer('user_id') : null,
                'action' => $request->string('action')->toString(),
                'entity_type' => $request->string('entity_type')->toString(),
                'date_from' => $request->string('date_from')->toString(),
                'date_to' => $request->string('date_to')->toString(),
            ],
            'status' => session('status'),
        ]);
    }

    public function show(Request $request, int $auditLog): Response
    {
        $auditLog = AuditLog::query()
            ->with('user')
            ->findOrFail($auditLog);

        $this->authorize('view', $auditLog);

        return Inertia::render('Admin/AuditLogs/Show', [
            'auditLog' => $this->presentAuditLogDetail($auditLog),
            'can' => [
                'viewIndex' => $request->user()->can('viewAny', AuditLog::class),
            ],
            'status' => session('status'),
        ]);
    }

    private function filteredAuditLogQuery(Request $request): Builder
    {
        return AuditLog::query()
            ->with('user')
            ->when($request->filled('user_id'), fn (Builder $query) => $query->where('user_id', $request->integer('user_id')))
            ->when($request->string('action')->toString() !== '', fn (Builder $query) => $query->where('action', 'like', '%'.$request->string('action')->toString().'%'))
            ->when($request->string('entity_type')->toString() !== '', fn (Builder $query) => $query->where('entity_type', 'like', '%'.$request->string('entity_type')->toString().'%'))
            ->when($request->string('date_from')->toString() !== '', fn (Builder $query) => $query->whereDate('created_at', '>=', $request->string('date_from')->toString()))
            ->when($request->string('date_to')->toString() !== '', fn (Builder $query) => $query->whereDate('created_at', '<=', $request->string('date_to')->toString()));
    }

    private function presentAuditLogSummary(AuditLog $auditLog): array
    {
        return [
            'id' => $auditLog->id,
            'action' => $auditLog->action,
            'entityType' => $auditLog->entity_type,
            'entityId' => $auditLog->entity_id,
            'createdAt' => $auditLog->created_at?->toIso8601String(),
            'user' => $auditLog->user === null ? null : [
                'id' => $auditLog->user->id,
                'name' => $auditLog->user->name,
                'email' => $auditLog->user->email,
            ],
            'metaPreview' => collect($auditLog->meta_json ?? [])
                ->take(4)
                ->all(),
        ];
    }

    private function presentAuditLogDetail(AuditLog $auditLog): array
    {
        return [
            'id' => $auditLog->id,
            'action' => $auditLog->action,
            'entityType' => $auditLog->entity_type,
            'entityId' => $auditLog->entity_id,
            'createdAt' => $auditLog->created_at?->toIso8601String(),
            'updatedAt' => $auditLog->updated_at?->toIso8601String(),
            'user' => $auditLog->user === null ? null : [
                'id' => $auditLog->user->id,
                'name' => $auditLog->user->name,
                'email' => $auditLog->user->email,
            ],
            'metaJson' => $auditLog->meta_json ?? [],
        ];
    }
}

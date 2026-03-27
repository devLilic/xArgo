<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogger
{
    /**
     * @param  array<string, mixed>  $metaJson
     */
    public function write(
        ?User $user,
        string $action,
        string $entityType,
        ?int $entityId = null,
        array $metaJson = [],
    ): AuditLog {
        return AuditLog::query()->create([
            'user_id' => $user?->id,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'meta_json' => $metaJson,
        ]);
    }
}

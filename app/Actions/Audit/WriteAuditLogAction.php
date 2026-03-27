<?php

namespace App\Actions\Audit;

use App\Models\AuditLog;
use App\Models\User;

class WriteAuditLogAction
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function execute(
        ?User $actor,
        string $event,
        string $targetType,
        ?int $targetId = null,
        array $metadata = [],
    ): AuditLog {
        return AuditLog::query()->create([
            'actor_id' => $actor?->id,
            'event' => $event,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'metadata' => $metadata,
        ]);
    }
}

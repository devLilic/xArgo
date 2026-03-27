<?php

namespace App\Actions\Audit;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\Audit\AuditLogger;

class WriteAuditLogAction
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {
    }

    /**
     * @param  array<string, mixed>  $metaJson
     */
    public function execute(
        ?User $user,
        string $action,
        string $entityType,
        ?int $entityId = null,
        array $metaJson = [],
    ): AuditLog {
        return $this->auditLogger->write($user, $action, $entityType, $entityId, $metaJson);
    }
}

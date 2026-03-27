<?php

namespace Tests\Unit\Services\Audit;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLoggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_writes_a_generic_audit_record(): void
    {
        $user = User::factory()->create();

        $auditLog = app(AuditLogger::class)->write(
            user: $user,
            action: 'admin.user.role_changed',
            entityType: 'user',
            entityId: 123,
            metaJson: [
                'from' => 'read_only',
                'to' => 'support',
            ],
        );

        $this->assertInstanceOf(AuditLog::class, $auditLog);
        $this->assertDatabaseHas('admin_audit_logs', [
            'id' => $auditLog->id,
            'user_id' => $user->id,
            'action' => 'admin.user.role_changed',
            'entity_type' => 'user',
            'entity_id' => 123,
        ]);
    }
}

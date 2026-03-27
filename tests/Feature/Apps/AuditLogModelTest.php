<?php

namespace Tests\Feature\Apps;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_audit_logs_table_exposes_the_required_schema(): void
    {
        $this->assertDatabaseEmpty('admin_audit_logs');

        foreach (['id', 'user_id', 'action', 'entity_type', 'entity_id', 'meta_json', 'created_at'] as $column) {
            $this->assertTrue(
                \Illuminate\Support\Facades\Schema::hasColumn('admin_audit_logs', $column),
                "Expected admin_audit_logs to contain column [{$column}].",
            );
        }
    }

    public function test_audit_log_belongs_to_user_and_casts_meta_json(): void
    {
        $user = User::factory()->create();

        $auditLog = AuditLog::query()->create([
            'user_id' => $user->id,
            'action' => 'admin.license.created',
            'entity_type' => 'license',
            'entity_id' => 42,
            'meta_json' => [
                'license_key' => 'XARGO-AUDIT-0001',
            ],
        ]);

        $this->assertSame($user->id, $auditLog->user->id);
        $this->assertSame('XARGO-AUDIT-0001', $auditLog->meta_json['license_key']);
    }
}

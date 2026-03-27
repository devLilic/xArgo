<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class AuditLogManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_support_users_can_filter_audit_logs_and_view_details(): void
    {
        $support = User::factory()->support()->create([
            'name' => 'Support Viewer',
            'email' => 'support@example.com',
        ]);
        $otherActor = User::factory()->superAdmin()->create([
            'name' => 'Super Admin',
            'email' => 'super@example.com',
        ]);

        $matchingLog = AuditLog::query()->create([
            'user_id' => $support->id,
            'action' => 'admin.license.updated',
            'entity_type' => 'license',
            'entity_id' => 41,
            'meta_json' => [
                'license_key' => 'XARGO-AUD-0001',
            ],
            'created_at' => now()->startOfDay(),
            'updated_at' => now()->startOfDay(),
        ]);

        AuditLog::query()->create([
            'user_id' => $otherActor->id,
            'action' => 'admin.user.role_changed',
            'entity_type' => 'user',
            'entity_id' => 7,
            'meta_json' => [
                'email' => 'other@example.com',
            ],
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        $this->actingAs($support)
            ->get(route('admin.audit-logs.index', [
                'user_id' => $support->id,
                'action' => 'license.updated',
                'entity_type' => 'license',
                'date_from' => now()->toDateString(),
                'date_to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/AuditLogs/Index')
                ->has('auditLogs', 1)
                ->where('auditLogs.0.id', $matchingLog->id)
                ->where('auditLogs.0.action', 'admin.license.updated')
                ->where('auditLogs.0.entityType', 'license')
                ->where('auditLogs.0.user.email', 'support@example.com')
            );

        $this->actingAs($support)
            ->get(route('admin.audit-logs.show', $matchingLog->id))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/AuditLogs/Show')
                ->where('auditLog.id', $matchingLog->id)
                ->where('auditLog.action', 'admin.license.updated')
                ->where('auditLog.entityType', 'license')
                ->where('auditLog.user.email', 'support@example.com')
                ->where('auditLog.metaJson.license_key', 'XARGO-AUD-0001')
            );
    }

    public function test_guests_are_redirected_from_audit_log_pages(): void
    {
        $auditLog = AuditLog::query()->create([
            'user_id' => User::factory()->create()->id,
            'action' => 'admin.license.created',
            'entity_type' => 'license',
            'entity_id' => 99,
            'meta_json' => [],
        ]);

        $this->get(route('admin.audit-logs.index'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.audit-logs.show', $auditLog->id))
            ->assertRedirect(route('login'));
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->dropForeign(['actor_id']);
            $table->dropIndex(['event', 'target_type']);
            $table->dropIndex(['target_type', 'target_id']);
        });

        Schema::rename('audit_logs', 'admin_audit_logs');

        Schema::table('admin_audit_logs', function (Blueprint $table): void {
            $table->renameColumn('actor_id', 'user_id');
            $table->renameColumn('event', 'action');
            $table->renameColumn('target_type', 'entity_type');
            $table->renameColumn('target_id', 'entity_id');
            $table->renameColumn('metadata', 'meta_json');
        });

        Schema::table('admin_audit_logs', function (Blueprint $table): void {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['action', 'entity_type']);
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('admin_audit_logs')) {
            return;
        }

        Schema::table('admin_audit_logs', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['action', 'entity_type']);
            $table->dropIndex(['entity_type', 'entity_id']);
        });

        Schema::table('admin_audit_logs', function (Blueprint $table): void {
            $table->renameColumn('user_id', 'actor_id');
            $table->renameColumn('action', 'event');
            $table->renameColumn('entity_type', 'target_type');
            $table->renameColumn('entity_id', 'target_id');
            $table->renameColumn('meta_json', 'metadata');
        });

        Schema::rename('admin_audit_logs', 'audit_logs');

        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->foreign('actor_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['event', 'target_type']);
            $table->index(['target_type', 'target_id']);
        });
    }
};

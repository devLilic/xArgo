<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_invitations', function (Blueprint $table): void {
            $table->index(['email', 'accepted_at'], 'user_invitations_email_accepted_idx');
            $table->index('expires_at', 'user_invitations_expires_at_idx');
        });

        Schema::table('licenses', function (Blueprint $table): void {
            $table->index(['status', 'expires_at'], 'licenses_status_expires_at_idx');
        });

        Schema::table('license_activations', function (Blueprint $table): void {
            $table->index('status', 'license_activations_status_idx');
            $table->index('installation_id', 'license_activations_installation_id_idx');
            $table->index('activation_token_hash', 'license_activations_token_hash_idx');
            $table->index(['last_reason_code', 'updated_at'], 'license_activations_reason_updated_idx');
        });

        Schema::table('license_heartbeats', function (Blueprint $table): void {
            $table->index('reason_code', 'license_heartbeats_reason_code_idx');
        });

        Schema::table('admin_audit_logs', function (Blueprint $table): void {
            $table->index(['user_id', 'created_at'], 'admin_audit_logs_user_created_idx');
            $table->index(['action', 'created_at'], 'admin_audit_logs_action_created_idx');
            $table->index('created_at', 'admin_audit_logs_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('user_invitations', function (Blueprint $table): void {
            $table->dropIndex('user_invitations_email_accepted_idx');
            $table->dropIndex('user_invitations_expires_at_idx');
        });

        Schema::table('licenses', function (Blueprint $table): void {
            $table->dropIndex('licenses_status_expires_at_idx');
        });

        Schema::table('license_activations', function (Blueprint $table): void {
            $table->dropIndex('license_activations_status_idx');
            $table->dropIndex('license_activations_installation_id_idx');
            $table->dropIndex('license_activations_token_hash_idx');
            $table->dropIndex('license_activations_reason_updated_idx');
        });

        Schema::table('license_heartbeats', function (Blueprint $table): void {
            $table->dropIndex('license_heartbeats_reason_code_idx');
        });

        Schema::table('admin_audit_logs', function (Blueprint $table): void {
            $table->dropIndex('admin_audit_logs_user_created_idx');
            $table->dropIndex('admin_audit_logs_action_created_idx');
            $table->dropIndex('admin_audit_logs_created_at_idx');
        });
    }
};

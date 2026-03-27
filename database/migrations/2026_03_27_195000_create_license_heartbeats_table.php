<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_heartbeats', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('license_activation_id')
                ->constrained('license_activations')
                ->cascadeOnDelete();
            $table->string('app_version', 64);
            $table->timestamp('received_at');
            $table->string('ip_address', 45)->nullable();
            $table->string('reason_code', 64)->nullable();

            $table->index(['license_activation_id', 'received_at'], 'license_heartbeats_activation_received_idx');
            $table->index('received_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_heartbeats');
    }
};

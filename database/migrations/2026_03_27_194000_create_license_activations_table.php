<?php

use App\Domain\Licensing\LicenseActivationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_activations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('license_id')->constrained('licenses')->cascadeOnDelete();
            $table->uuid('activation_id')->unique();
            $table->string('machine_id');
            $table->uuid('installation_id');
            $table->string('activation_token_hash', 64);
            $table->string('device_label')->nullable();
            $table->string('status')->default(LicenseActivationStatus::ACTIVE->value);
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('grace_until')->nullable();
            $table->string('last_reason_code')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['license_id', 'status']);
            $table->index(['machine_id', 'installation_id']);
            $table->index('last_seen_at');
            $table->index('grace_until');
            $table->index('last_reason_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_activations');
    }
};

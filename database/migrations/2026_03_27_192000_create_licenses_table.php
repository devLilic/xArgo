<?php

use App\Domain\Licensing\LicenseStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('app_id')->constrained('apps')->restrictOnDelete();
            $table->foreignId('plan_id')->constrained('license_plans')->restrictOnDelete();
            $table->string('public_key')->unique();
            $table->string('license_key')->unique();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('status')->default(LicenseStatus::ACTIVE->value);
            $table->unsignedInteger('max_devices')->default(1);
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('grace_hours')->default(24);
            $table->text('notes')->nullable();
            $table->timestamp('last_validated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['app_id', 'status']);
            $table->index(['plan_id', 'status']);
            $table->index('customer_email');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};

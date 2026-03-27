<?php

use App\Domain\Licensing\LicenseDurationType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_plans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('app_id')->constrained('apps')->restrictOnDelete();
            $table->string('name');
            $table->string('code');
            $table->string('duration_type');
            $table->unsignedInteger('duration_days')->nullable();
            $table->unsignedInteger('default_max_devices')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['app_id', 'code']);
            $table->index(['app_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_plans');
    }
};

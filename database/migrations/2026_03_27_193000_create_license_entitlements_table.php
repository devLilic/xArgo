<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_entitlements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('license_id')->constrained('licenses')->cascadeOnDelete();
            $table->string('feature_code');
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(['license_id', 'feature_code']);
            $table->index(['feature_code', 'enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_entitlements');
    }
};

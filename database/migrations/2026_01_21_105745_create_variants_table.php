<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('variants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('vehicle_model_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // Create pivot table for variant-fuel_type relationship
        Schema::create('variant_fuel_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained()->onDelete('cascade');
            $table->foreignId('fuel_type_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['variant_id', 'fuel_type_id'], 'variant_fuel_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variant_fuel_type');
        Schema::dropIfExists('variants');
    }
};

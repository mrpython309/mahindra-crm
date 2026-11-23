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
        Schema::create('vehicle_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('segment_id')->constrained('segments')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('transmission_id')->constrained('transmissions')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('fuel_type_id')->constrained('fuel_types')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('dealer_location_id')->constrained('dealer_locations')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('company_id')->constrained('companies')->onUpdate('cascade')->onDelete('restrict');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['segment_id', 'transmission_id', 'fuel_type_id', 'status'], 'models_search_idx');
            $table->unique(['name', 'company_id'], 'unique_model_per_company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_models');
    }
};

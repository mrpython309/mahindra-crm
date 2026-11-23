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
        // Vehicle Model - Fuel Types
        Schema::create('vehicle_model_fuel_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_model_id')->constrained()->onDelete('cascade');
            $table->foreignId('fuel_type_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['vehicle_model_id', 'fuel_type_id'], 'vm_fuel_unique');
        });

        // Vehicle Model - Transmissions
        Schema::create('vehicle_model_transmission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_model_id')->constrained()->onDelete('cascade');
            $table->foreignId('transmission_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['vehicle_model_id', 'transmission_id'], 'vm_transmission_unique');
        });

        // Vehicle Model - Companies
        Schema::create('vehicle_model_company', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_model_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['vehicle_model_id', 'company_id'], 'vm_company_unique');
        });

        // Vehicle Model - Dealer Locations
        Schema::create('vehicle_model_dealer_location', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_model_id')->constrained()->onDelete('cascade');
            $table->foreignId('dealer_location_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['vehicle_model_id', 'dealer_location_id'], 'vm_dealer_location_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_model_dealer_location');
        Schema::dropIfExists('vehicle_model_company');
        Schema::dropIfExists('vehicle_model_transmission');
        Schema::dropIfExists('vehicle_model_fuel_type');
    }
};

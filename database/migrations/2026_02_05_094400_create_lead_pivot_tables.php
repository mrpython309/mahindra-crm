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
        // Pivot table for lead-variant relationship
        Schema::create('lead_variant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('variant_id')->constrained('variants')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['lead_id', 'variant_id']);
        });

        // Pivot table for lead-fuel_type relationship
        Schema::create('lead_fuel_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('fuel_type_id')->constrained('fuel_types')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['lead_id', 'fuel_type_id']);
        });

        // Pivot table for lead-transmission relationship
        Schema::create('lead_transmission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('transmission_id')->constrained('transmissions')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['lead_id', 'transmission_id']);
        });

        // Pivot table for lead-colour relationship
        Schema::create('lead_colour', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('colour_id')->constrained('colours')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['lead_id', 'colour_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_colour');
        Schema::dropIfExists('lead_transmission');
        Schema::dropIfExists('lead_fuel_type');
        Schema::dropIfExists('lead_variant');
    }
};

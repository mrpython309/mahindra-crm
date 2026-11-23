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
        Schema::create('colour_vehicle_model', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_model_id')->constrained()->onDelete('cascade');
            $table->foreignId('colour_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['vehicle_model_id', 'colour_id'], 'vm_colour_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colour_vehicle_model');
    }
};

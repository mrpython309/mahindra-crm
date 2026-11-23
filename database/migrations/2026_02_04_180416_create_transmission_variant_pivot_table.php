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
        Schema::create('transmission_variant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained()->onDelete('cascade');
            $table->foreignId('transmission_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['variant_id', 'transmission_id'], 'variant_transmission_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transmission_variant');
    }
};

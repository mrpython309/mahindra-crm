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
        Schema::create('colour_variant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained()->onDelete('cascade');
            $table->foreignId('colour_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['variant_id', 'colour_id'], 'variant_colour_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colour_variant');
    }
};

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
        Schema::table('vehicle_models', function (Blueprint $table) {
            // Unique constraint 'unique_model_per_company' was already dropped in a previous migration
            
            // Add a simple unique constraint on just the model name
            // Since we now use many-to-many, a model can be associated with multiple companies
            $table->unique('name', 'unique_model_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_models', function (Blueprint $table) {
            // Remove the new unique constraint
            $table->dropUnique('unique_model_name');
            
            // Note: Cannot restore the original constraint as company_id column doesn't exist
        });
    }
};

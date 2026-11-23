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
            // 1. Create a temporary index on segment_id so MySQL can use it to satisfy the segment_id foreign key
            $table->index('segment_id', 'temp_segment_idx');

            // 2. Drop foreign key constraints
            $table->dropForeign(['transmission_id']);
            $table->dropForeign(['fuel_type_id']);
            $table->dropForeign(['dealer_location_id']);
            $table->dropForeign(['company_id']);

            // 3. Drop index and unique constraint (now safe because temp_segment_idx satisfies the segment_id constraint)
            $table->dropIndex('models_search_idx');
            $table->dropUnique('unique_model_per_company');
            
            // 4. Drop the columns
            $table->dropColumn(['transmission_id', 'fuel_type_id', 'dealer_location_id', 'company_id']);

            // 5. Recreate search index without the deleted columns
            $table->index(['segment_id', 'status'], 'models_search_idx');

            // 6. Drop the temporary index (the recreated models_search_idx now satisfies the segment_id constraint)
            $table->dropIndex('temp_segment_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_models', function (Blueprint $table) {
            // 1. Create a temporary index on segment_id
            $table->index('segment_id', 'temp_segment_idx');

            // 2. Drop modified search index
            $table->dropIndex('models_search_idx');

            // 3. Add back the columns
            $table->foreignId('transmission_id')->nullable()->constrained();
            $table->foreignId('fuel_type_id')->nullable()->constrained();
            $table->foreignId('dealer_location_id')->nullable()->constrained();
            $table->foreignId('company_id')->nullable()->constrained();

            // 4. Restore original index and unique constraint
            $table->index(['segment_id', 'transmission_id', 'fuel_type_id', 'status'], 'models_search_idx');
            $table->unique(['name', 'company_id'], 'unique_model_per_company');

            // 5. Drop the temporary index
            $table->dropIndex('temp_segment_idx');
        });
    }
};

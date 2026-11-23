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
        // Drop foreign key constraint first (try different constraint name formats)
        try {
            Schema::table('status_sources', function (Blueprint $table) {
                $table->dropForeign(['lead_source_id']);
            });
        } catch (\Exception $e) {
            // Try alternative constraint name
            try {
                Schema::table('status_sources', function (Blueprint $table) {
                    $table->dropForeign('status_sources_lead_source_id_foreign');
                });
            } catch (\Exception $e2) {
                // Constraint might not exist or already dropped
            }
        }

        // Drop the column if it exists
        if (Schema::hasColumn('status_sources', 'lead_source_id')) {
            Schema::table('status_sources', function (Blueprint $table) {
                $table->dropColumn('lead_source_id');
            });
        }

        // Add the new response column if it doesn't exist
        if (!Schema::hasColumn('status_sources', 'response')) {
            Schema::table('status_sources', function (Blueprint $table) {
                $table->string('response')->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop response column if it exists
        if (Schema::hasColumn('status_sources', 'response')) {
            Schema::table('status_sources', function (Blueprint $table) {
                $table->dropColumn('response');
            });
        }

        // Add lead_source_id back if it doesn't exist
        if (!Schema::hasColumn('status_sources', 'lead_source_id')) {
            Schema::table('status_sources', function (Blueprint $table) {
                $table->foreignId('lead_source_id')->constrained()->cascadeOnDelete();
            });
        }
    }
};

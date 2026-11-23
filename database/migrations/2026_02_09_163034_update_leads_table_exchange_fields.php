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
        Schema::table('leads', function (Blueprint $table) {
            // Drop old columns
            if (Schema::hasColumn('leads', 'exchange_vehicle_model')) {
                $table->dropColumn('exchange_vehicle_model');
            }
            if (Schema::hasColumn('leads', 'exchange_transmission')) {
                $table->dropColumn('exchange_transmission');
            }

            // Update exchange_fuel_type to exchange_fuel_type_id (foreign key)
            if (Schema::hasColumn('leads', 'exchange_fuel_type')) {
                $table->dropColumn('exchange_fuel_type');
            }

            // Add new columns
            if (!Schema::hasColumn('leads', 'exchange_make_id')) {
                $table->foreignId('exchange_make_id')->nullable()->after('purchase_type')->constrained('makes')->onDelete('set null');
            }
            if (!Schema::hasColumn('leads', 'exchange_brand_model_id')) {
                $table->foreignId('exchange_brand_model_id')->nullable()->after('exchange_make_id')->constrained('brand_models')->onDelete('set null');
            }
            if (!Schema::hasColumn('leads', 'exchange_fuel_type_id')) {
                $table->foreignId('exchange_fuel_type_id')->nullable()->after('exchange_brand_model_id')->constrained('fuel_types')->onDelete('set null');
            }
            if (!Schema::hasColumn('leads', 'exchange_kms')) {
                $table->string('exchange_kms')->nullable()->after('exchange_fuel_type_id');
            }
            if (!Schema::hasColumn('leads', 'exchange_no_of_owners')) {
                $table->integer('exchange_no_of_owners')->nullable()->after('exchange_kms');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Drop new columns
            if (Schema::hasColumn('leads', 'exchange_no_of_owners')) {
                $table->dropColumn('exchange_no_of_owners');
            }
            if (Schema::hasColumn('leads', 'exchange_kms')) {
                $table->dropColumn('exchange_kms');
            }
            if (Schema::hasColumn('leads', 'exchange_fuel_type_id')) {
                $table->dropForeign(['exchange_fuel_type_id']);
                $table->dropColumn('exchange_fuel_type_id');
            }
            if (Schema::hasColumn('leads', 'exchange_brand_model_id')) {
                $table->dropForeign(['exchange_brand_model_id']);
                $table->dropColumn('exchange_brand_model_id');
            }
            if (Schema::hasColumn('leads', 'exchange_make_id')) {
                $table->dropForeign(['exchange_make_id']);
                $table->dropColumn('exchange_make_id');
            }

            // Restore old columns
            if (!Schema::hasColumn('leads', 'exchange_vehicle_model')) {
                $table->string('exchange_vehicle_model')->nullable();
            }
            if (!Schema::hasColumn('leads', 'exchange_transmission')) {
                $table->string('exchange_transmission')->nullable();
            }
            if (!Schema::hasColumn('leads', 'exchange_fuel_type')) {
                $table->string('exchange_fuel_type')->nullable();
            }
        });
    }
};

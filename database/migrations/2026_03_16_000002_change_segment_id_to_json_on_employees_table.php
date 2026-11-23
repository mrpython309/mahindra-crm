<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Drop the foreign key constraint
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['segment_id']);
        });

        // Step 2: Drop the old integer column
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('segment_id');
        });

        // Step 3: Add the new json column
        Schema::table('employees', function (Blueprint $table) {
            $table->json('segment_id')->nullable()->after('company_id');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('segment_id');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('segment_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }
};

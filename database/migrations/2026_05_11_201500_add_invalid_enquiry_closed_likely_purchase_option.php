<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE leads MODIFY likely_purchase ENUM('under_15_days','15_to_45_days','above_45_days','invalid_enquiry_closed') NULL"
        );

        DB::table('lead_types')->updateOrInsert(
            ['name' => 'Invalid Enquiry (Closed)'],
            ['likely_purchase' => 'invalid_enquiry_closed']
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('lead_types')
            ->where('name', 'Invalid Enquiry (Closed)')
            ->delete();

        DB::statement(
            "ALTER TABLE leads MODIFY likely_purchase ENUM('under_15_days','15_to_45_days','above_45_days') NULL"
        );
    }
};

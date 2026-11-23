<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE lead_statuses MODIFY name ENUM('active','closed','duplicate') NOT NULL");

        if (!DB::table('lead_statuses')->where('name', 'duplicate')->exists()) {
            DB::table('lead_statuses')->insert([
                'name'       => 'duplicate',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('lead_statuses')->where('name', 'duplicate')->delete();
        DB::statement("ALTER TABLE lead_statuses MODIFY name ENUM('active','closed') NOT NULL");
    }
};

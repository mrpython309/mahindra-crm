<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Extend the existing enum on leads.status to allow 'closed'
        DB::statement("ALTER TABLE leads MODIFY status ENUM('active','inactive','closed') NOT NULL DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to the original enum definition without 'closed'
        DB::statement("ALTER TABLE leads MODIFY status ENUM('active','inactive') NOT NULL DEFAULT 'active'");
    }
};


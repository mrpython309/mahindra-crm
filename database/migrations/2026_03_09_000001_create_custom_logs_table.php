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
        Schema::create('custom_logs', function (Blueprint $table) {
            $table->id();
            $table->string('context')->nullable(); // e.g. company_import, employee_import
            $table->string('level')->default('info'); // info, warning, error
            $table->string('message');
            $table->json('data')->nullable(); // arbitrary structured payload (row, errors, etc.)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_logs');
    }
};


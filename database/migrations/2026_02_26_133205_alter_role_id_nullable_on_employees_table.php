<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->integer('role_id')->nullable()->change();
            $table->foreignId('segment_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->integer('role_id')->nullable(false)->change();
            $table->foreignId('segment_id')->nullable(false)->change();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lead_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('assignment_type', ['tele_caller', 'sales_consultant']);
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['lead_id', 'user_id', 'assignment_type'], 'lead_user_type_unique');
        });

        // Backfill from existing leads.assigned_to
        // Treat existing assigned_to as sales consultant and use created_by (or assigned_to) as tele-caller.
        if (Schema::hasTable('leads')) {
            DB::table('leads')
                ->whereNotNull('assigned_to')
                ->orderBy('id')
                ->chunkById(100, function ($leads) {
                    $now = now();
                    $insertSales = [];
                    $insertTele = [];

                    foreach ($leads as $lead) {
                        // Sales consultant assignment (existing behaviour)
                        $insertSales[] = [
                            'lead_id'         => $lead->id,
                            'user_id'         => $lead->assigned_to,
                            'assignment_type' => 'sales_consultant',
                            'assigned_by'     => $lead->created_by,
                            'created_at'      => $now,
                            'updated_at'      => $now,
                        ];

                        // Tele-caller: prefer created_by, fallback to assigned_to
                        $teleCallerUserId = $lead->created_by ?: $lead->assigned_to;
                        if ($teleCallerUserId) {
                            $insertTele[] = [
                                'lead_id'         => $lead->id,
                                'user_id'         => $teleCallerUserId,
                                'assignment_type' => 'tele_caller',
                                'assigned_by'     => $lead->created_by,
                                'created_at'      => $now,
                                'updated_at'      => $now,
                            ];
                        }
                    }

                    if (!empty($insertSales)) {
                        DB::table('lead_assignments')->insertOrIgnore($insertSales);
                    }
                    if (!empty($insertTele)) {
                        DB::table('lead_assignments')->insertOrIgnore($insertTele);
                    }
                });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_assignments');
    }
};


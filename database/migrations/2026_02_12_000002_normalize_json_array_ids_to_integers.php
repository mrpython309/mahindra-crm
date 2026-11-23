<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Normalize JSON array columns to store IDs as integers instead of strings.
     */
    public function up(): void
    {
        $columns = ['department_id', 'dealer_location_id', 'subsegment_id', 'reporting_to'];

        foreach (['employees', 'users'] as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            foreach ($columns as $col) {
                if (!Schema::hasColumn($table, $col)) {
                    continue;
                }
                $rows = DB::table($table)->whereNotNull($col)->where($col, '!=', '')->get(['id', $col]);
                foreach ($rows as $row) {
                    $decoded = json_decode($row->{$col});
                    if (!is_array($decoded)) {
                        continue;
                    }
                    $normalized = array_values(array_map('intval', array_filter($decoded)));
                    DB::table($table)->where('id', $row->id)->update([
                        $col => json_encode($normalized),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migration (cannot reliably convert back to strings).
     */
    public function down(): void
    {
        // No-op: normalization is one-way for data consistency
    }
};

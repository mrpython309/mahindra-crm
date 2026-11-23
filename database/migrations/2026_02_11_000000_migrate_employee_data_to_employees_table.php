<?php

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // If employees table does not exist, nothing to migrate.
        if (!Schema::hasTable('employees')) {
            return;
        }

        User::whereNotNull('emp_code')
            ->chunkById(100, function ($users) {
                foreach ($users as $user) {
                    // Skip if an employee is already linked to this user
                    $existingForUser = Employee::where('user_id', $user->id)->first();
                    if ($existingForUser) {
                        continue;
                    }

                    // Skip if an employee with the same emp_code already exists
                    $existingByCode = Employee::where('emp_code', $user->emp_code)->first();
                    if ($existingByCode) {
                        if (is_null($existingByCode->user_id) && $user->is_user === 'yes') {
                            $existingByCode->user_id = $user->id;
                            $existingByCode->save();
                        }
                        continue;
                    }

                    Employee::create([
                        'emp_code'           => $user->emp_code,
                        'mile_id'            => $user->mile_id,
                        'employee_name'      => $user->name,
                        'company_id'         => $user->company_id ?? 0,
                        'is_left'            => $user->is_left ?? 'no',
                        'is_user'            => $user->is_user ?? 'no',
                        'designation_id'     => $user->designation_id,
                        'segment_id'         => $user->segment_id,
                        'department_id'      => $user->department_id ?? [],
                        'dealer_location_id' => $user->dealer_location_id ?? [],
                        'subsegment_id'      => $user->subsegment_id ?? [],
                        'reporting_to'       => $user->reporting_to ?? [],
                        'mobile_number'      => $user->mobile_number,
                        'email'              => $user->email,
                        'date_of_joining'    => $user->date_of_joining,
                        'employee_status'    => $user->employee_status ?? 'active',
                        'left_date'          => $user->left_date,
                        'role_id'            => $user->role_id,
                        'user_id'            => $user->is_user === 'yes' ? $user->id : null,
                    ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left empty: data migration is not reversed automatically.
    }
};


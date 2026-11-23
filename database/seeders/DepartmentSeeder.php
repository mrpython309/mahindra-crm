<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Seed core departments (masters).
     */
    public function run(): void
    {
        $departments = [
            'Sales',
            'Service',
            'Spare Parts',
            'Finance & Insurance',
            'Customer Relations',
            'Admin',
            'Human Resources',
            'Accounts',
        ];

        foreach ($departments as $name) {
            Department::updateOrCreate(
                ['department_name' => $name],
                ['status' => 'active']
            );
        }
    }
}


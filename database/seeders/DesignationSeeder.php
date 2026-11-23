<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Designation;

class DesignationSeeder extends Seeder
{
    /**
     * Seed core designations (masters).
     */
    public function run(): void
    {
        $designations = [
            'Tele-caller',
            'Sales Consultant',
            'Senior Sales Consultant',
            'Team Leader - Sales',
            'Sales Manager',
            'Branch Manager',
            'General Manager',
            'CRM Executive',
            'Service Advisor',
            'Technician',
        ];

        foreach ($designations as $name) {
            Designation::updateOrCreate(
                ['designation_name' => $name],
                ['status' => 'active']
            );
        }
    }
}


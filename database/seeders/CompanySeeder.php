<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            ['name' => 'Mahindra & Mahindra', 'code' => 'MAH', 'status' => 'active'],
            ['name' => 'Mahindra Electric', 'code' => 'MEL', 'status' => 'active'],
            ['name' => 'SsangYong Motor', 'code' => 'SSY', 'status' => 'active'],
        ];

        foreach ($companies as $company) {
            Company::updateOrCreate(
                ['name' => $company['name']],
                $company
            );
        }

        $this->command->info('Company seeder completed successfully.');
    }
}
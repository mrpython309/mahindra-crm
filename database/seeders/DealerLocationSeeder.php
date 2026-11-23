<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DealerLocation;

class DealerLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First get available companies
        $companies = \App\Models\Company::all();
        
        if ($companies->isEmpty()) {
            $this->command->error('No companies found. Please seed companies first.');
            return;
        }
        
        $dealerLocations = [
            ['name' => 'Mumbai - Andheri', 'code' => 'MUM-AND', 'address' => 'Andheri West, Mumbai', 'city' => 'Mumbai', 'state' => 'Maharashtra', 'status' => 'active'],
            ['name' => 'Delhi - Connaught Place', 'code' => 'DEL-CP', 'address' => 'CP, New Delhi', 'city' => 'Delhi', 'state' => 'Delhi', 'status' => 'active'],
            ['name' => 'Bangalore - Whitefield', 'code' => 'BLR-WHT', 'address' => 'Whitefield, Bangalore', 'city' => 'Bangalore', 'state' => 'Karnataka', 'status' => 'active'],
            ['name' => 'Chennai - OMR', 'code' => 'CHE-OMR', 'address' => 'OMR, Chennai', 'city' => 'Chennai', 'state' => 'Tamil Nadu', 'status' => 'active'],
            ['name' => 'Pune - Baner', 'code' => 'PUN-BAN', 'address' => 'Baner, Pune', 'city' => 'Pune', 'state' => 'Maharashtra', 'status' => 'active'],
            ['name' => 'Hyderabad - Gachibowli', 'code' => 'HYD-GAC', 'address' => 'Gachibowli, Hyderabad', 'city' => 'Hyderabad', 'state' => 'Telangana', 'status' => 'active'],
        ];

        foreach ($dealerLocations as $locationData) {
            // Assign a random company to each dealer location
            $company = $companies->random();
            $locationData['company_id'] = $company->id;
            
            DealerLocation::updateOrCreate(
                ['code' => $locationData['code']],
                $locationData
            );
        }

        $this->command->info('Dealer Location seeder completed successfully.');
    }
}
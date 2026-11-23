<?php

namespace Database\Seeders;

use App\Models\State;
use Illuminate\Database\Seeder;

class StatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $states = [
            ['name' => 'Andhra Pradesh', 'code' => 'AP', 'status' => 'active'],
            ['name' => 'Arunachal Pradesh', 'code' => 'AR', 'status' => 'active'],
            ['name' => 'Assam', 'code' => 'AS', 'status' => 'active'],
            ['name' => 'Bihar', 'code' => 'BR', 'status' => 'active'],
            ['name' => 'Chhattisgarh', 'code' => 'CG', 'status' => 'active'],
            ['name' => 'Goa', 'code' => 'GA', 'status' => 'active'],
            ['name' => 'Gujarat', 'code' => 'GJ', 'status' => 'active'],
            ['name' => 'Haryana', 'code' => 'HR', 'status' => 'active'],
            ['name' => 'Himachal Pradesh', 'code' => 'HP', 'status' => 'active'],
            ['name' => 'Jharkhand', 'code' => 'JH', 'status' => 'active'],
            ['name' => 'Karnataka', 'code' => 'KA', 'status' => 'active'],
            ['name' => 'Kerala', 'code' => 'KL', 'status' => 'active'],
            ['name' => 'Madhya Pradesh', 'code' => 'MP', 'status' => 'active'],
            ['name' => 'Maharashtra', 'code' => 'MH', 'status' => 'active'],
            ['name' => 'Manipur', 'code' => 'MN', 'status' => 'active'],
            ['name' => 'Meghalaya', 'code' => 'ML', 'status' => 'active'],
            ['name' => 'Mizoram', 'code' => 'MZ', 'status' => 'active'],
            ['name' => 'Nagaland', 'code' => 'NL', 'status' => 'active'],
            ['name' => 'Odisha', 'code' => 'OD', 'status' => 'active'],
            ['name' => 'Punjab', 'code' => 'PB', 'status' => 'active'],
            ['name' => 'Rajasthan', 'code' => 'RJ', 'status' => 'active'],
            ['name' => 'Sikkim', 'code' => 'SK', 'status' => 'active'],
            ['name' => 'Tamil Nadu', 'code' => 'TN', 'status' => 'active'],
            ['name' => 'Telangana', 'code' => 'TS', 'status' => 'active'],
            ['name' => 'Tripura', 'code' => 'TR', 'status' => 'active'],
            ['name' => 'Uttar Pradesh', 'code' => 'UP', 'status' => 'active'],
            ['name' => 'Uttarakhand', 'code' => 'UK', 'status' => 'active'],
            ['name' => 'West Bengal', 'code' => 'WB', 'status' => 'active'],
            // Union Territories
            ['name' => 'Andaman and Nicobar Islands', 'code' => 'AN', 'status' => 'active'],
            ['name' => 'Chandigarh', 'code' => 'CH', 'status' => 'active'],
            ['name' => 'Dadra and Nagar Haveli and Daman and Diu', 'code' => 'DD', 'status' => 'active'],
            ['name' => 'Delhi', 'code' => 'DL', 'status' => 'active'],
            ['name' => 'Jammu and Kashmir', 'code' => 'JK', 'status' => 'active'],
            ['name' => 'Ladakh', 'code' => 'LA', 'status' => 'active'],
            ['name' => 'Lakshadweep', 'code' => 'LD', 'status' => 'active'],
            ['name' => 'Puducherry', 'code' => 'PY', 'status' => 'active'],
        ];

        foreach ($states as $state) {
            State::create($state);
        }
    }
}

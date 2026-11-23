<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FuelType;

class FuelTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      $fuelTypes = [
        ['name' => 'Petrol',   'status' => 'active'],
        ['name' => 'Diesel',   'status' => 'active'],
        ['name' => 'Electric', 'status' => 'active'],
        ['name' => 'CNG',      'status' => 'active'],
      ];

      foreach ($fuelTypes as $fuelType) {
        FuelType::updateOrCreate(
          ['name' => $fuelType['name']],
          $fuelType
        );
      }
    }
}

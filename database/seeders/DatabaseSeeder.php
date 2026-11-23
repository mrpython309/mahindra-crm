<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    // User::factory(10)->create();

    // Super Admin
    $superAdmin = User::firstOrCreate(
      ['email' => 'admin@gmail.com'],
      [
        'name' => 'Super Admin',
        'password' => Hash::make('123456'),
      ]
    );

    $this->call([
      LocationSeeder::class,
      SegmentSeeder::class,
      ColourSeeder::class,
      TransmissionSeeder::class,
      FuelTypeSeeder::class,
      CompanySeeder::class,
      DealerLocationSeeder::class,
      VehicleModelSeeder::class,
      VariantSeeder::class,
      LeadMasterSeeder::class,
      StatusSourceSeeder::class,
      DepartmentSeeder::class,
      DesignationSeeder::class,
      RolesPermissionSeeder::class,
      EmployeeJsonSeeder::class,
    ]);

  }
}

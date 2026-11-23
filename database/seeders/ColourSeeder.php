<?php

namespace Database\Seeders;

use App\Models\Colour;
use Illuminate\Database\Seeder;

class ColourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colours = [
            ['name' => 'White', 'status' => 'active'],
            ['name' => 'Silver', 'status' => 'active'],
            ['name' => 'Black', 'status' => 'active'],
            ['name' => 'Red', 'status' => 'active'],
            ['name' => 'Blue', 'status' => 'active'],
            ['name' => 'Grey', 'status' => 'active'],
            ['name' => 'Brown', 'status' => 'active'],
            ['name' => 'Orange', 'status' => 'active'],
        ];

        foreach ($colours as $colour) {
            Colour::updateOrCreate(
                ['name' => $colour['name']],
                $colour
            );
        }
    }
}


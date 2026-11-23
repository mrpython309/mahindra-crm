<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\VehicleModel;
use App\Models\Segment;
use App\Models\Transmission;
use App\Models\FuelType;
use App\Models\DealerLocation;
use App\Models\Company;
use App\Models\Colour;

class VehicleModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get related entities
        $segments = Segment::all();
        $transmissions = Transmission::all();
        $fuelTypes = FuelType::all();
        $colours = Colour::all();
        $dealerLocations = DealerLocation::all();
        $companies = Company::all();

        if ($segments->isEmpty() || $transmissions->isEmpty() || $fuelTypes->isEmpty() ||
            $dealerLocations->isEmpty() || $companies->isEmpty() || $colours->isEmpty()) {
            $this->command->error('Required entities not found. Please run other seeders first.');
            return;
        }

        $vehicleModels = [
            [
                'name' => 'XUV700',
                'segment_id' => $segments->where('name', 'Personal Vehicle')->first()->id,
                'status' => 'active'
            ],
            [
                'name' => 'Scorpio N',
                'segment_id' => $segments->where('name', 'Personal Vehicle')->first()->id,
                'status' => 'active'
            ],
            [
                'name' => 'XUV400',
                'segment_id' => $segments->where('name', 'Electric Vehicle')->first()->id,
                'status' => 'active'
            ],
            [
                'name' => 'Bolero',
                'segment_id' => $segments->where('name', 'Commercial Vehicle')->first()->id,
                'status' => 'active'
            ],
            [
                'name' => 'Thar',
                'segment_id' => $segments->where('name', 'Personal Vehicle')->first()->id,
                'status' => 'active'
            ],
        ];

        foreach ($vehicleModels as $modelData) {
            // Pick one transmission, fuel type, dealer location and company for pivot relations
            $selectedTransmission = $transmissions->random();
            $selectedDealerLocation = $dealerLocations->random();
            $selectedCompany = $companies->random();

            // Ensure EV gets Electric fuel type when available
            if ($modelData['name'] === 'XUV400') {
                $electricFuel = $fuelTypes->firstWhere('name', 'Electric');
                $selectedFuelType = $electricFuel ?: $fuelTypes->random();
            } else {
                $nonElectricFuels = $fuelTypes->where('name', '!=', 'Electric');
                $selectedFuelType = $nonElectricFuels->isNotEmpty()
                    ? $nonElectricFuels->random()
                    : $fuelTypes->random();
            }

            // Create/update the vehicle model (company / transmission / fuel are now handled via pivot tables)
            $model = VehicleModel::updateOrCreate(
                [
                    'name' => $modelData['name'],
                    'segment_id' => $modelData['segment_id'],
                ],
                [
                    'segment_id' => $modelData['segment_id'],
                    'status' => $modelData['status'],
                ]
            );

            // Attach **multiple options** for this model so it can be reused across variants/leads

            // 1) Transmissions – give every model all available transmissions
            $model->transmissions()->sync($transmissions->pluck('id'));

            // 2) Fuel types – EV models only get Electric, others get all non‑Electric fuels
            if ($modelData['name'] === 'XUV400' && isset($electricFuel) && $electricFuel) {
                $model->fuelTypes()->sync([$electricFuel->id]);
            } else {
                $allowedFuelTypes = $fuelTypes->where('name', '!=', 'Electric');
                $model->fuelTypes()->sync(
                    ($allowedFuelTypes->isNotEmpty() ? $allowedFuelTypes : $fuelTypes)->pluck('id')
                );
            }

            // 3) Colours – keep 2‑4 colours per model
            $selectedColours = $colours->random(
                min(max(2, 1), min(4, $colours->count()))
            );
            $model->colours()->sync($selectedColours->pluck('id'));

            // 4) Companies / Dealer locations – link model to all dealers of its company
            //    (handy for filtering by location later)
            $model->companies()->syncWithoutDetaching([$selectedCompany->id]);
            $model->dealerLocations()->sync($dealerLocations->pluck('id'));

            $this->command->info("Created vehicle model: {$model->name}");
        }

        $this->command->info('Vehicle Model seeder completed successfully.');
    }
}

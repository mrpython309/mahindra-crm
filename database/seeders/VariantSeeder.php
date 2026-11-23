<?php

namespace Database\Seeders;

use App\Models\Variant;
use App\Models\VehicleModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Load all vehicle models with their option sets so every model gets variants
        $vehicleModels = VehicleModel::with(['fuelTypes', 'transmissions', 'colours'])->get();

        if ($vehicleModels->isEmpty()) {
            $this->command->info('No vehicle models found. Please seed vehicle models first.');
            return;
        }

        // Define sensible variants per Mahindra model; fallback to generic ones
        $defaultVariants = [
            'XUV700'   => ['MX', 'AX3', 'AX5', 'AX7'],
            'Scorpio N'=> ['Z2', 'Z4', 'Z6', 'Z8'],
            'XUV400'   => ['EC Pro', 'EL Pro'],
            'Bolero'   => ['B4', 'B6', 'B6(O)'],
            'Thar'     => ['AX(O)', 'LX'],
        ];

        foreach ($vehicleModels as $vehicleModel) {
            $names = $defaultVariants[$vehicleModel->name] ?? ['Base', 'Mid', 'Top'];

            foreach ($names as $name) {
                $variant = Variant::updateOrCreate(
                    [
                        'name' => $name,
                        'vehicle_model_id' => $vehicleModel->id,
                    ],
                    [
                        'status' => 'active',
                    ]
                );

                // Attach all available fuel types from the vehicle model
                $availableFuelTypes = $vehicleModel->fuelTypes;
                if ($availableFuelTypes->count() > 0) {
                    $variant->fuelTypes()->sync(
                        $availableFuelTypes->pluck('id')
                    );
                }

                // Attach all available transmissions from the vehicle model
                $availableTransmissions = $vehicleModel->transmissions;
                if ($availableTransmissions->count() > 0) {
                    $variant->transmissions()->sync(
                        $availableTransmissions->pluck('id')
                    );
                }

                // Attach some colours (2–4) from the vehicle model
                $availableColours = $vehicleModel->colours;
                if ($availableColours->count() > 0) {
                    $selectedColours = $availableColours->random(
                        min(max(2, 1), min(4, $availableColours->count()))
                    );
                    $variant->colours()->sync(
                        $selectedColours->pluck('id')
                    );
                }

                $this->command->info("Created/updated variant: {$variant->name} for {$vehicleModel->name}");
            }
        }
    }
}

<?php

namespace App\Jobs;

use App\Models\Colour;
use App\Models\CustomLog;
use App\Models\FuelType;
use App\Models\Transmission;
use App\Models\Variant;
use App\Models\VehicleModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProcessVariantImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $path;
    protected string $batchId;

    public function __construct(string $path, string $batchId)
    {
        $this->path = $path;
        $this->batchId = $batchId;
    }

    public function handle(): void
    {
        $fullPath = Storage::path($this->path);

        if (!is_readable($fullPath)) {
            CustomLog::create([
                'context' => 'variant_import',
                'level' => 'error',
                'message' => 'Unable to read uploaded file for variant import',
                'data' => ['batch_id' => $this->batchId, 'path' => $this->path],
            ]);
            return;
        }

        $handle = fopen($fullPath, 'r');
        if ($handle === false) {
            CustomLog::create([
                'context' => 'variant_import',
                'level' => 'error',
                'message' => 'Unable to open uploaded file for variant import',
                'data' => ['batch_id' => $this->batchId, 'path' => $this->path],
            ]);
            return;
        }

        $expectedHeader = [
            'vehicle_model_name',
            'name',
            'fuel_type_names',
            'transmission_names',
            'colour_names',
            'status',
        ];

        $header = null;
        while (($header = fgetcsv($handle, 0, ',')) !== false) {
            $trimmed = array_filter(array_map('trim', $header), static fn($v) => $v !== '');
            if (count($trimmed) === 0) continue;
            $header = array_map('trim', $header);
            break;
        }

        if ($header === null) {
            fclose($handle);
            CustomLog::create([
                'context' => 'variant_import',
                'level' => 'error',
                'message' => 'The uploaded file is empty or has no header row.',
                'data' => ['batch_id' => $this->batchId, 'path' => $this->path],
            ]);
            return;
        }

        if ($header !== $expectedHeader) {
            fclose($handle);
            CustomLog::create([
                'context' => 'variant_import',
                'level' => 'error',
                'message' => 'Header mismatch. Please use the provided sample CSV template.',
                'data' => [
                    'batch_id' => $this->batchId,
                    'path' => $this->path,
                    'received_header' => $header,
                    'expected_header' => $expectedHeader,
                ],
            ]);
            return;
        }

        $rowNumber = 1;
        $inserted = 0;
        $duplicates = 0;
        $invalid = 0;

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $rowNumber++;
            if ($row === [null] || count(array_filter($row, static fn($v) => trim((string) $v) !== '')) === 0) continue;
            if (count($row) < count($expectedHeader)) $row = array_pad($row, count($expectedHeader), null);
            $row = array_slice($row, 0, count($expectedHeader));
            $data = array_combine($expectedHeader, $row);
            $data = array_map(static fn($v) => is_string($v) ? trim($v) : $v, $data);

            $validator = Validator::make($data, [
                'vehicle_model_name' => 'required|string|max:255',
                'name' => 'required|string|max:255',
                'fuel_type_names' => 'required|string',
                'transmission_names' => 'required|string',
                'colour_names' => 'nullable|string',
                'status' => 'required|in:active,inactive',
            ]);

            if ($validator->fails()) {
                $invalid++;
                CustomLog::create([
                    'context' => 'variant_import',
                    'level' => 'warning',
                    'message' => 'Skipped invalid row during variant import',
                    'data' => [
                        'batch_id' => $this->batchId,
                        'row_number' => $rowNumber,
                        'row' => $data,
                        'errors' => $validator->errors()->all(),
                    ],
                ]);
                continue;
            }

            $vehicleModel = VehicleModel::where('name', $data['vehicle_model_name'])->first();
            if (!$vehicleModel) {
                $invalid++;
                CustomLog::create([
                    'context' => 'variant_import',
                    'level' => 'warning',
                    'message' => 'Skipped row: vehicle model not found',
                    'data' => [
                        'batch_id' => $this->batchId,
                        'row_number' => $rowNumber,
                        'row' => $data,
                        'unknown' => ['vehicle_model_name' => $data['vehicle_model_name']],
                    ],
                ]);
                continue;
            }

            $vehicleModel->load(['fuelTypes', 'transmissions']);
            $allowedFuelNames = $vehicleModel->fuelTypes->pluck('name')->toArray();
            $allowedTransmissionNames = $vehicleModel->transmissions->pluck('name')->toArray();

            $fuelTypeNames = $this->parseCsvColumn($data['fuel_type_names']);
            $transmissionNames = $this->parseCsvColumn($data['transmission_names']);
            $colourNames = $this->parseCsvColumn($data['colour_names'] ?? '');

            $missing = [];
            foreach ($fuelTypeNames as $n) {
                if (!in_array($n, $allowedFuelNames)) $missing['fuel_type_names'][] = $n;
            }
            foreach ($transmissionNames as $n) {
                if (!in_array($n, $allowedTransmissionNames)) $missing['transmission_names'][] = $n;
            }
            if ($missing !== [] || empty($fuelTypeNames) || empty($transmissionNames)) {
                $invalid++;
                CustomLog::create([
                    'context' => 'variant_import',
                    'level' => 'warning',
                    'message' => 'Skipped row: fuel types or transmissions must belong to the vehicle model',
                    'data' => [
                        'batch_id' => $this->batchId,
                        'row_number' => $rowNumber,
                        'row' => $data,
                        'unknown' => $missing !== [] ? $missing : 'At least one fuel type and one transmission required.',
                    ],
                ]);
                continue;
            }

            $fuelTypeIds = FuelType::whereIn('name', $fuelTypeNames)->pluck('id')->toArray();
            $transmissionIds = Transmission::whereIn('name', $transmissionNames)->pluck('id')->toArray();
            $colourIds = [];
            if (!empty($colourNames)) {
                $colourIds = Colour::whereIn('name', $colourNames)->pluck('id')->toArray();
            }

            if (Variant::where('vehicle_model_id', $vehicleModel->id)->where('name', $data['name'])->exists()) {
                $duplicates++;
                CustomLog::create([
                    'context' => 'variant_import',
                    'level' => 'warning',
                    'message' => 'Skipped duplicate variant during import',
                    'data' => [
                        'batch_id' => $this->batchId,
                        'row_number' => $rowNumber,
                        'row' => $data,
                        'errors' => ['Variant with this name already exists for this vehicle model. Row skipped.'],
                    ],
                ]);
                continue;
            }

            try {
                DB::transaction(function () use ($data, $vehicleModel, $fuelTypeIds, $transmissionIds, $colourIds) {
                    $variant = Variant::create([
                        'name' => $data['name'],
                        'vehicle_model_id' => $vehicleModel->id,
                        'status' => $data['status'],
                    ]);
                    $variant->fuelTypes()->attach($fuelTypeIds);
                    $variant->transmissions()->attach($transmissionIds);
                    if (!empty($colourIds)) {
                        $variant->colours()->attach($colourIds);
                    }
                });
                $inserted++;
            } catch (\Throwable $e) {
                $invalid++;
                CustomLog::create([
                    'context' => 'variant_import',
                    'level' => 'warning',
                    'message' => 'Skipped row: database error during variant import',
                    'data' => [
                        'batch_id' => $this->batchId,
                        'row_number' => $rowNumber,
                        'row' => $data,
                        'error' => $e->getMessage(),
                    ],
                ]);
            }
        }

        fclose($handle);

        $totalProcessed = $inserted + $duplicates + $invalid;
        $message = "Import completed. Processed {$totalProcessed} rows. Inserted {$inserted} variants.";
        if ($duplicates > 0) $message .= " Skipped {$duplicates} duplicate rows.";
        if ($invalid > 0) $message .= " Skipped {$invalid} invalid rows.";

        CustomLog::create([
            'context' => 'variant_import',
            'level' => 'info',
            'message' => $message,
            'data' => [
                'batch_id' => $this->batchId,
                'inserted' => $inserted,
                'duplicates' => $duplicates,
                'invalid' => $invalid,
                'total_processed' => $totalProcessed,
            ],
        ]);
    }

    private function parseCsvColumn(string $value): array
    {
        $parts = array_map('trim', explode(',', $value));
        return array_values(array_filter($parts, static fn($v) => $v !== ''));
    }
}

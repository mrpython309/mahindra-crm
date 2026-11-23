<?php

namespace App\Jobs;

use App\Models\Colour;
use App\Models\Company;
use App\Models\CustomLog;
use App\Models\DealerLocation;
use App\Models\FuelType;
use App\Models\Segment;
use App\Models\Transmission;
use App\Models\VehicleModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProcessVehicleModelImport implements ShouldQueue
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

        if (! is_readable($fullPath)) {
            CustomLog::create([
                'context' => 'vehicle_model_import',
                'level' => 'error',
                'message' => 'Unable to read uploaded file for vehicle model import',
                'data' => [
                    'batch_id' => $this->batchId,
                    'path' => $this->path,
                ],
            ]);
            return;
        }

        $handle = fopen($fullPath, 'r');

        if ($handle === false) {
            CustomLog::create([
                'context' => 'vehicle_model_import',
                'level' => 'error',
                'message' => 'Unable to open uploaded file for vehicle model import',
                'data' => [
                    'batch_id' => $this->batchId,
                    'path' => $this->path,
                ],
            ]);
            return;
        }

        $expectedHeader = [
            'name',
            'segment_code',
            'status',
            'transmission_names',
            'fuel_type_names',
            'colour_names',
            'company_codes',
            'dealer_location_codes',
        ];

        $header = null;
        while (($header = fgetcsv($handle, 0, ',')) !== false) {
            $trimmed = array_filter(array_map('trim', $header), static function ($value) {
                return $value !== '';
            });

            if (count($trimmed) === 0) {
                continue;
            }

            $header = array_map('trim', $header);
            break;
        }

        if ($header === null) {
            fclose($handle);

            CustomLog::create([
                'context' => 'vehicle_model_import',
                'level' => 'error',
                'message' => 'The uploaded file is empty or has no header row.',
                'data' => [
                    'batch_id' => $this->batchId,
                    'path' => $this->path,
                ],
            ]);
            return;
        }

        if ($header !== $expectedHeader) {
            fclose($handle);

            CustomLog::create([
                'context' => 'vehicle_model_import',
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

            if ($row === [null] || count(array_filter($row, static fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            if (count($row) < count($expectedHeader)) {
                $row = array_pad($row, count($expectedHeader), null);
            }

            $row = array_slice($row, 0, count($expectedHeader));

            $data = array_combine($expectedHeader, $row);

            $data = array_map(static function ($value) {
                return is_string($value) ? trim($value) : $value;
            }, $data);

            $validator = Validator::make($data, [
                'name' => 'required|string|max:255',
                'segment_code' => 'required|string|max:50',
                'status' => 'required|in:active,inactive',
                'transmission_names' => 'required|string',
                'fuel_type_names' => 'required|string',
                'colour_names' => 'required|string',
                'company_codes' => 'required|string',
                'dealer_location_codes' => 'required|string',
            ]);

            if ($validator->fails()) {
                $invalid++;
                $messages = $validator->errors()->all();

                CustomLog::create([
                    'context' => 'vehicle_model_import',
                    'level' => 'warning',
                    'message' => 'Skipped invalid row during vehicle model import',
                    'data' => [
                        'batch_id' => $this->batchId,
                        'row_number' => $rowNumber,
                        'row' => $data,
                        'errors' => $messages,
                    ],
                ]);
                continue;
            }

            $segment = Segment::where('code', $data['segment_code'])->first();
            if (! $segment) {
                $invalid++;
                CustomLog::create([
                    'context' => 'vehicle_model_import',
                    'level' => 'warning',
                    'message' => 'Skipped row: segment code not found',
                    'data' => [
                        'batch_id' => $this->batchId,
                        'row_number' => $rowNumber,
                        'row' => $data,
                        'unknown' => ['segment_code' => $data['segment_code']],
                    ],
                ]);
                continue;
            }

            $transmissionNames = $this->parseCsvColumn($data['transmission_names']);
            $fuelTypeNames = $this->parseCsvColumn($data['fuel_type_names']);
            $colourNames = $this->parseCsvColumn($data['colour_names']);
            $companyCodes = $this->parseCsvColumn($data['company_codes']);
            $dealerLocationCodes = $this->parseCsvColumn($data['dealer_location_codes']);

            $transmissionIds = Transmission::whereIn('name', $transmissionNames)->pluck('id')->toArray();
            $fuelTypeIds = FuelType::whereIn('name', $fuelTypeNames)->pluck('id')->toArray();
            $colourIds = Colour::whereIn('name', $colourNames)->pluck('id')->toArray();
            $companyIds = Company::whereIn('code', $companyCodes)->pluck('id')->toArray();
            $dealerLocationIds = DealerLocation::whereIn('code', $dealerLocationCodes)->pluck('id')->toArray();

            $missing = [];
            if (count($transmissionNames) !== count($transmissionIds)) {
                $found = Transmission::whereIn('name', $transmissionNames)->pluck('name')->toArray();
                $missing['transmission_names'] = array_values(array_diff($transmissionNames, $found));
            }
            if (count($fuelTypeNames) !== count($fuelTypeIds)) {
                $found = FuelType::whereIn('name', $fuelTypeNames)->pluck('name')->toArray();
                $missing['fuel_type_names'] = array_values(array_diff($fuelTypeNames, $found));
            }
            if (count($colourNames) !== count($colourIds)) {
                $found = Colour::whereIn('name', $colourNames)->pluck('name')->toArray();
                $missing['colour_names'] = array_values(array_diff($colourNames, $found));
            }
            if (count($companyCodes) !== count($companyIds)) {
                $found = Company::whereIn('code', $companyCodes)->pluck('code')->toArray();
                $missing['company_codes'] = array_values(array_diff($companyCodes, $found));
            }
            if (count($dealerLocationCodes) !== count($dealerLocationIds)) {
                $found = DealerLocation::whereIn('code', $dealerLocationCodes)->pluck('code')->toArray();
                $missing['dealer_location_codes'] = array_values(array_diff($dealerLocationCodes, $found));
            }

            if ($missing !== []) {
                $invalid++;
                CustomLog::create([
                    'context' => 'vehicle_model_import',
                    'level' => 'warning',
                    'message' => 'Skipped row: one or more relation names/codes not found',
                    'data' => [
                        'batch_id' => $this->batchId,
                        'row_number' => $rowNumber,
                        'row' => $data,
                        'unknown' => $missing,
                    ],
                ]);
                continue;
            }

            if (count($transmissionIds) < 1 || count($fuelTypeIds) < 1 || count($colourIds) < 1 || count($companyIds) < 1 || count($dealerLocationIds) < 1) {
                $invalid++;
                CustomLog::create([
                    'context' => 'vehicle_model_import',
                    'level' => 'warning',
                    'message' => 'Skipped row: each relation must have at least one value',
                    'data' => [
                        'batch_id' => $this->batchId,
                        'row_number' => $rowNumber,
                        'row' => $data,
                    ],
                ]);
                continue;
            }

            if (VehicleModel::where('name', $data['name'])->exists()) {
                $duplicates++;
                CustomLog::create([
                    'context' => 'vehicle_model_import',
                    'level' => 'warning',
                    'message' => 'Skipped duplicate vehicle model during import',
                    'data' => [
                        'batch_id' => $this->batchId,
                        'row_number' => $rowNumber,
                        'row' => $data,
                        'errors' => ['Vehicle model with this name already exists. Row skipped.'],
                    ],
                ]);
                continue;
            }

            try {
                DB::transaction(function () use ($data, $segment, $transmissionIds, $fuelTypeIds, $colourIds, $companyIds, $dealerLocationIds) {
                    $vehicleModel = VehicleModel::create([
                        'name' => $data['name'],
                        'segment_id' => $segment->id,
                        'status' => $data['status'],
                    ]);
                    $vehicleModel->transmissions()->attach($transmissionIds);
                    $vehicleModel->fuelTypes()->attach($fuelTypeIds);
                    $vehicleModel->colours()->attach($colourIds);
                    $vehicleModel->companies()->attach($companyIds);
                    $vehicleModel->dealerLocations()->attach($dealerLocationIds);
                });
                $inserted++;
            } catch (\Throwable $e) {
                $invalid++;
                CustomLog::create([
                    'context' => 'vehicle_model_import',
                    'level' => 'warning',
                    'message' => 'Skipped row: database error during vehicle model import',
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

        $message = "Import completed. Processed {$totalProcessed} rows. Inserted {$inserted} vehicle models.";

        if ($duplicates > 0) {
            $message .= " Skipped {$duplicates} duplicate rows.";
        }

        if ($invalid > 0) {
            $message .= " Skipped {$invalid} invalid rows.";
        }

        CustomLog::create([
            'context' => 'vehicle_model_import',
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

    /**
     * Parse a CSV column value into an array of non-empty trimmed strings.
     *
     * @return array<int, string>
     */
    private function parseCsvColumn(string $value): array
    {
        $parts = array_map('trim', explode(',', $value));

        return array_values(array_filter($parts, static fn ($v) => $v !== ''));
    }
}

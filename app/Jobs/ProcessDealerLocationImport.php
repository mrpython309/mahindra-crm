<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\CustomLog;
use App\Models\DealerLocation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProcessDealerLocationImport implements ShouldQueue
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
                'context' => 'dealer_location_import',
                'level' => 'error',
                'message' => 'Unable to read uploaded file for dealer location import',
                'data' => ['batch_id' => $this->batchId, 'path' => $this->path],
            ]);
            return;
        }

        $handle = fopen($fullPath, 'r');
        if ($handle === false) {
            CustomLog::create([
                'context' => 'dealer_location_import',
                'level' => 'error',
                'message' => 'Unable to open uploaded file for dealer location import',
                'data' => ['batch_id' => $this->batchId, 'path' => $this->path],
            ]);
            return;
        }

        $expectedHeader = [
            'name',
            'code',
            'company_code',
            'address',
            'city',
            'state',
            'country',
            'contact_number',
            'email',
            'status',
        ];

        $header = null;
        while (($header = fgetcsv($handle, 0, ',')) !== false) {
            $trimmed = array_filter(array_map('trim', $header), fn($v) => $v !== '');
            if (count($trimmed) === 0) {
                continue;
            }
            $header = array_map('trim', $header);
            break;
        }

        if ($header === null) {
            fclose($handle);
            CustomLog::create([
                'context' => 'dealer_location_import',
                'level' => 'error',
                'message' => 'The uploaded file is empty or has no header row.',
                'data' => ['batch_id' => $this->batchId, 'path' => $this->path],
            ]);
            return;
        }

        if ($header !== $expectedHeader) {
            fclose($handle);
            CustomLog::create([
                'context' => 'dealer_location_import',
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

            if ($row === [null] || count(array_filter($row, fn($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            if (count($row) < count($expectedHeader)) {
                $row = array_pad($row, count($expectedHeader), null);
            }
            $row = array_slice($row, 0, count($expectedHeader));
            $data = array_combine($expectedHeader, $row);
            $data = array_map(fn($v) => is_string($v) ? trim($v) : $v, $data);

            $validator = Validator::make($data, [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50',
                'company_code' => 'required|string|max:50',
                'address' => 'nullable|string',
                'city' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
                'contact_number' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'status' => 'required|in:active,inactive',
            ]);

            if ($validator->fails()) {
                $invalid++;
                $messages = $validator->errors()->all();
                CustomLog::create([
                    'context' => 'dealer_location_import',
                    'level' => 'warning',
                    'message' => 'Skipped invalid row during dealer location import',
                    'data' => [
                        'batch_id' => $this->batchId,
                        'row_number' => $rowNumber,
                        'row' => $data,
                        'errors' => $messages,
                    ],
                ]);
                continue;
            }

            $company = Company::where('code', $data['company_code'])->first();
            if (!$company) {
                $invalid++;
                $messages = ["Company not found for code: {$data['company_code']}. Use a valid company code from the Companies master."];
                CustomLog::create([
                    'context' => 'dealer_location_import',
                    'level' => 'warning',
                    'message' => 'Skipped row: company not found',
                    'data' => [
                        'batch_id' => $this->batchId,
                        'row_number' => $rowNumber,
                        'row' => $data,
                        'errors' => $messages,
                    ],
                ]);
                continue;
            }

            if (DealerLocation::where('code', $data['code'])->exists()) {
                $duplicates++;
                $messages = ['Dealer location with this code already exists. Row skipped.'];
                CustomLog::create([
                    'context' => 'dealer_location_import',
                    'level' => 'warning',
                    'message' => 'Skipped duplicate dealer location during import',
                    'data' => [
                        'batch_id' => $this->batchId,
                        'row_number' => $rowNumber,
                        'row' => $data,
                        'errors' => $messages,
                    ],
                ]);
                continue;
            }

            DealerLocation::create([
                'name' => $data['name'],
                'code' => $data['code'],
                'company_id' => $company->id,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'country' => $data['country'] ?? null,
                'contact_number' => $data['contact_number'] ?? null,
                'email' => $data['email'] ?? null,
                'status' => $data['status'],
            ]);
            $inserted++;
        }

        fclose($handle);

        $totalProcessed = $inserted + $duplicates + $invalid;
        $message = "Import completed. Processed {$totalProcessed} rows. Inserted {$inserted} dealer locations.";
        if ($duplicates > 0) {
            $message .= " Skipped {$duplicates} duplicate rows.";
        }
        if ($invalid > 0) {
            $message .= " Skipped {$invalid} invalid rows.";
        }

        CustomLog::create([
            'context' => 'dealer_location_import',
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
}

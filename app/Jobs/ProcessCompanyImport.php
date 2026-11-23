<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\CustomLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProcessCompanyImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $path;
    protected string $batchId;

    /**
     * @param string $path Stored path to the CSV file (on default disk)
     * @param string $batchId Identifier to group log entries for this import
     */
    public function __construct(string $path, string $batchId)
    {
        $this->path = $path;
        $this->batchId = $batchId;
    }

    /**
     * Handle the job.
     */
    public function handle(): void
    {
        $fullPath = Storage::path($this->path);

        if (!is_readable($fullPath)) {
            CustomLog::create([
                'context' => 'company_import',
                'level' => 'error',
                'message' => 'Unable to read uploaded file for company import',
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
                'context' => 'company_import',
                'level' => 'error',
                'message' => 'Unable to open uploaded file for company import',
                'data' => [
                    'batch_id' => $this->batchId,
                    'path' => $this->path,
                ],
            ]);
            return;
        }

        $expectedHeader = [
            'name',
            'code',
            'address_line_1',
            'address_line_2',
            'city',
            'state',
            'country',
            'pincode',
            'contact_person',
            'contact_number',
            'email',
            'status',
        ];

        $header = null;
        while (($header = fgetcsv($handle, 0, ',')) !== false) {
            $trimmed = array_filter(array_map('trim', $header), function ($value) {
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
                'context' => 'company_import',
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
                'context' => 'company_import',
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

            if ($row === [null] || count(array_filter($row, fn($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            if (count($row) < count($expectedHeader)) {
                $row = array_pad($row, count($expectedHeader), null);
            }

            $row = array_slice($row, 0, count($expectedHeader));

            $data = array_combine($expectedHeader, $row);

            $data = array_map(function ($value) {
                return is_string($value) ? trim($value) : $value;
            }, $data);

            $validator = Validator::make($data, [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50',
                'address_line_1' => 'nullable|string|max:255',
                'address_line_2' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
                'pincode' => 'nullable|string|max:20',
                'contact_person' => 'nullable|string|max:255',
                'contact_number' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'status' => 'required|in:active,inactive',
            ]);

            if ($validator->fails()) {
                $invalid++;
                $messages = $validator->errors()->all();

                CustomLog::create([
                    'context' => 'company_import',
                    'level' => 'warning',
                    'message' => 'Skipped invalid row during company import',
                    'data' => [
                        'batch_id' => $this->batchId,
                        'row_number' => $rowNumber,
                        'row' => $data,
                        'errors' => $messages,
                    ],
                ]);
                continue;
            }

            $exists = Company::where('code', $data['code'])->exists();

            if ($exists) {
                $duplicates++;
                $messages = ['Company with this code already exists. Row skipped.'];

                CustomLog::create([
                    'context' => 'company_import',
                    'level' => 'warning',
                    'message' => 'Skipped duplicate company during import',
                    'data' => [
                        'batch_id' => $this->batchId,
                        'row_number' => $rowNumber,
                        'row' => $data,
                        'errors' => $messages,
                    ],
                ]);
                continue;
            }

            Company::create($data);
            $inserted++;
        }

        fclose($handle);

        $totalProcessed = $inserted + $duplicates + $invalid;

        $message = "Import completed. Processed {$totalProcessed} rows. Inserted {$inserted} companies.";

        if ($duplicates > 0) {
            $message .= " Skipped {$duplicates} duplicate rows.";
        }

        if ($invalid > 0) {
            $message .= " Skipped {$invalid} invalid rows.";
        }

        CustomLog::create([
            'context' => 'company_import',
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


<?php

namespace App\Jobs;

use App\Models\CustomLog;
use App\Models\Department;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProcessDepartmentImport implements ShouldQueue
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
                'context' => 'department_import',
                'level' => 'error',
                'message' => 'Unable to read uploaded file for department import',
                'data' => ['batch_id' => $this->batchId, 'path' => $this->path],
            ]);
            return;
        }

        $handle = fopen($fullPath, 'r');
        if ($handle === false) {
            CustomLog::create([
                'context' => 'department_import',
                'level' => 'error',
                'message' => 'Unable to open uploaded file for department import',
                'data' => ['batch_id' => $this->batchId, 'path' => $this->path],
            ]);
            return;
        }

        $expectedHeader = ['department_name', 'status'];

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
                'context' => 'department_import',
                'level' => 'error',
                'message' => 'The uploaded file is empty or has no header row.',
                'data' => ['batch_id' => $this->batchId, 'path' => $this->path],
            ]);
            return;
        }

        if ($header !== $expectedHeader) {
            fclose($handle);
            CustomLog::create([
                'context' => 'department_import',
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
                'department_name' => 'required|string|max:255',
                'status' => 'required|in:active,inactive',
            ]);

            if ($validator->fails()) {
                $invalid++;
                CustomLog::create([
                    'context' => 'department_import',
                    'level' => 'warning',
                    'message' => 'Skipped invalid row during department import',
                    'data' => [
                        'batch_id' => $this->batchId,
                        'row_number' => $rowNumber,
                        'row' => $data,
                        'errors' => $validator->errors()->all(),
                    ],
                ]);
                continue;
            }

            if (Department::where('department_name', $data['department_name'])->exists()) {
                $duplicates++;
                CustomLog::create([
                    'context' => 'department_import',
                    'level' => 'warning',
                    'message' => 'Skipped duplicate department during import',
                    'data' => [
                        'batch_id' => $this->batchId,
                        'row_number' => $rowNumber,
                        'row' => $data,
                        'errors' => ['Department with this name already exists. Row skipped.'],
                    ],
                ]);
                continue;
            }

            Department::create($data);
            $inserted++;
        }

        fclose($handle);

        $totalProcessed = $inserted + $duplicates + $invalid;
        $message = "Import completed. Processed {$totalProcessed} rows. Inserted {$inserted} departments.";
        if ($duplicates > 0) $message .= " Skipped {$duplicates} duplicate rows.";
        if ($invalid > 0) $message .= " Skipped {$invalid} invalid rows.";

        CustomLog::create([
            'context' => 'department_import',
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

<?php

namespace App\Jobs;

use App\Models\CustomLog;
use App\Models\Segment;
use App\Models\SubSegment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProcessSubSegmentImport implements ShouldQueue
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
                'context' => 'subsegment_import',
                'level' => 'error',
                'message' => 'Unable to read uploaded file for subsegment import',
                'data' => ['batch_id' => $this->batchId, 'path' => $this->path],
            ]);
            return;
        }

        $handle = fopen($fullPath, 'r');
        if ($handle === false) {
            CustomLog::create([
                'context' => 'subsegment_import',
                'level' => 'error',
                'message' => 'Unable to open uploaded file for subsegment import',
                'data' => ['batch_id' => $this->batchId, 'path' => $this->path],
            ]);
            return;
        }

        $expectedHeader = ['segment_code', 'subsegment_name', 'status'];

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
                'context' => 'subsegment_import',
                'level' => 'error',
                'message' => 'The uploaded file is empty or has no header row.',
                'data' => ['batch_id' => $this->batchId, 'path' => $this->path],
            ]);
            return;
        }

        if ($header !== $expectedHeader) {
            fclose($handle);
            CustomLog::create([
                'context' => 'subsegment_import',
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
                'segment_code' => 'required|string|max:50',
                'subsegment_name' => 'required|string|max:255',
                'status' => 'required|in:active,inactive',
            ]);

            if ($validator->fails()) {
                $invalid++;
                CustomLog::create([
                    'context' => 'subsegment_import',
                    'level' => 'warning',
                    'message' => 'Skipped invalid row during subsegment import',
                    'data' => [
                        'batch_id' => $this->batchId,
                        'row_number' => $rowNumber,
                        'row' => $data,
                        'errors' => $validator->errors()->all(),
                    ],
                ]);
                continue;
            }

            $segment = Segment::where('code', $data['segment_code'])->first();
            if (!$segment) {
                $invalid++;
                CustomLog::create([
                    'context' => 'subsegment_import',
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

            if (SubSegment::where('segment_id', $segment->id)->where('subsegment_name', $data['subsegment_name'])->exists()) {
                $duplicates++;
                CustomLog::create([
                    'context' => 'subsegment_import',
                    'level' => 'warning',
                    'message' => 'Skipped duplicate subsegment during import',
                    'data' => [
                        'batch_id' => $this->batchId,
                        'row_number' => $rowNumber,
                        'row' => $data,
                        'errors' => ['Subsegment with this name already exists for this segment. Row skipped.'],
                    ],
                ]);
                continue;
            }

            SubSegment::create([
                'segment_id' => $segment->id,
                'subsegment_name' => $data['subsegment_name'],
                'status' => $data['status'],
            ]);
            $inserted++;
        }

        fclose($handle);

        $totalProcessed = $inserted + $duplicates + $invalid;
        $message = "Import completed. Processed {$totalProcessed} rows. Inserted {$inserted} subsegments.";
        if ($duplicates > 0) $message .= " Skipped {$duplicates} duplicate rows.";
        if ($invalid > 0) $message .= " Skipped {$invalid} invalid rows.";

        CustomLog::create([
            'context' => 'subsegment_import',
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

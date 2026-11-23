<?php

namespace App\Jobs;

use App\Models\CustomLog;
use App\Models\EnquiryType;
use App\Models\Lead;
use App\Models\LeadImportBatch;
use App\Models\LeadSource;
use App\Models\VehicleModel;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class ProcessLeadImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $path;
    protected string $batchId;
    protected int $createdBy;
    protected ?LeadImportBatch $batch = null;

    public function __construct(string $path, string $batchId, int $createdBy)
    {
        $this->path = $path;
        $this->batchId = $batchId;
        $this->createdBy = $createdBy;
    }

    public function handle(): void
    {
        $this->batch = LeadImportBatch::query()->firstOrCreate(
            ['batch_id' => $this->batchId],
            [
                'file_path' => $this->path,
                'created_by' => $this->createdBy,
                'status' => 'queued',
            ]
        );
        $this->batch->update([
            'file_path' => $this->path,
            'created_by' => $this->createdBy,
            'status' => 'processing',
            'started_at' => now(),
            'completed_at' => null,
            'error_message' => null,
        ]);

        $handle = null;
        $inserted = 0;
        $duplicates = 0;
        $invalid = 0;
        $totalProcessed = 0;

        try {
        $fullPath = Storage::path($this->path);

        if (!is_readable($fullPath)) {
            $this->log('error', 'Unable to read uploaded file for lead import', [
                'event' => 'file_unreadable',
                'path' => $this->path,
            ]);
            $this->markFailed('Unable to read uploaded file for lead import');
            return;
        }

        $handle = fopen($fullPath, 'r');
        if ($handle === false) {
            $this->log('error', 'Unable to open uploaded file for lead import', [
                'event' => 'file_open_failed',
                'path' => $this->path,
            ]);
            $this->markFailed('Unable to open uploaded file for lead import');
            return;
        }

        $delimiter = $this->detectLeadImportCsvDelimiter($handle);
        rewind($handle);

        $expectedHeader = [
            'enquiry_type',
            'lead_source',
            'first_name',
            'last_name',
            'mobile_no',
            'email',
            'city',
            'vehicle_model',
            'lead_date',
        ];

        $header = null;
        while (($header = fgetcsv($handle, 0, $delimiter)) !== false) {
            $trimmed = array_filter(array_map('trim', $header), fn($v) => $v !== '');
            if (count($trimmed) === 0) {
                continue;
            }
            $header = $this->normalizeLeadImportHeaderRow($header);
            break;
        }

        if ($header === null) {
            fclose($handle);
            $handle = null;
            $this->log('error', 'The uploaded file is empty or has no header row.', [
                'event' => 'header_missing',
                'path' => $this->path,
            ]);
            $this->markFailed('The uploaded file is empty or has no header row.');
            return;
        }

        if ($header !== $expectedHeader) {
            fclose($handle);
            $handle = null;
            $this->log('error', 'Header mismatch. Please use the provided sample CSV template.', [
                'event' => 'header_mismatch',
                'path' => $this->path,
                'delimiter' => $delimiter,
                'received_header' => $header,
                'received_header_lengths' => array_map(static fn ($h) => strlen((string) $h), $header),
                'expected_header' => $expectedHeader,
            ]);
            $this->markFailed('Header mismatch. Please use the provided sample CSV template.');
            return;
        }

        $rowNumber = 1;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNumber++;

            if ($row === [null] || count(array_filter($row, fn($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }
            $totalProcessed++;

            if (count($row) < count($expectedHeader)) {
                $row = array_pad($row, count($expectedHeader), null);
            }
            $row = array_slice($row, 0, count($expectedHeader));
            $data = array_combine($expectedHeader, $row);
            $data = array_map(fn($v) => is_string($v) ? trim($v) : $v, $data);
            foreach (['enquiry_type', 'lead_source', 'first_name', 'last_name', 'email', 'city', 'vehicle_model'] as $squishField) {
                if (isset($data[$squishField]) && is_string($data[$squishField])) {
                    $data[$squishField] = Str::squish($data[$squishField]);
                }
            }

            $validator = Validator::make($data, [
                'enquiry_type' => 'required|string|max:255',
                'lead_source'  => 'required|string|max:255',
                'first_name'   => 'required|string|max:255',
                'last_name'    => 'nullable|string|max:255',
                'mobile_no'    => 'required|string|max:20',
                'email'        => 'nullable|email|max:255',
                'city'         => 'nullable|string|max:100',
                'vehicle_model' => 'nullable|string|max:255',
                'lead_date'    => 'required|date|before_or_equal:today',
            ]);

            if ($validator->fails()) {
                $invalid++;
                $this->log('warning', 'Skipped invalid row during lead import', [
                    'event' => 'row_validation_failed',
                    'row_number' => $rowNumber,
                    'row' => $data,
                    'errors' => $validator->errors()->all(),
                ]);
                continue;
            }

            // Resolve enquiry_type name → ID
            $enquiryType = EnquiryType::whereRaw('LOWER(name) = ?', [strtolower($data['enquiry_type'])])->first();
            if (!$enquiryType) {
                $invalid++;
                $this->log('warning', 'Skipped row: enquiry type not found', [
                    'event' => 'row_enquiry_type_not_found',
                    'row_number' => $rowNumber,
                    'row' => $data,
                    'errors' => ["Enquiry type '{$data['enquiry_type']}' not found. Use a valid enquiry type name."],
                ]);
                continue;
            }

            // Resolve lead_source campaign → ID
            $leadSource = LeadSource::whereRaw('LOWER(campaign) = ?', [strtolower($data['lead_source'])])->first();
            if (!$leadSource) {
                $invalid++;
                $this->log('warning', 'Skipped row: lead source not found', [
                    'event' => 'row_lead_source_not_found',
                    'row_number' => $rowNumber,
                    'row' => $data,
                    'errors' => ["Lead source '{$data['lead_source']}' not found. Use a valid lead source name."],
                ]);
                continue;
            }

            // Resolve vehicle_model name → ID (nullable)
            $vehicleModelId = null;
            if (!empty($data['vehicle_model'])) {
                $vehicleModel = VehicleModel::whereRaw('LOWER(name) = ?', [strtolower($data['vehicle_model'])])->first();
                if (!$vehicleModel) {
                    $invalid++;
                    $this->log('warning', 'Skipped row: vehicle model not found', [
                        'event' => 'row_vehicle_model_not_found',
                        'row_number' => $rowNumber,
                        'row' => $data,
                        'errors' => ["Vehicle model '{$data['vehicle_model']}' not found. Use a valid vehicle model name or leave blank."],
                    ]);
                    continue;
                }
                $vehicleModelId = $vehicleModel->id;
            }

            // Duplicate check by mobile_no
            if (Lead::where('mobile_no', $data['mobile_no'])->exists()) {
                $duplicates++;
                $this->log('warning', 'Skipped duplicate lead during import', [
                    'event' => 'row_duplicate',
                    'row_number' => $rowNumber,
                    'row' => $data,
                    'errors' => ["A lead with mobile number '{$data['mobile_no']}' already exists. Row skipped."],
                ]);
                continue;
            }

            $leadDate = Carbon::parse($data['lead_date'])->startOfDay();

            $lead = Lead::create([
                'first_name'      => $data['first_name'],
                'last_name'       => $data['last_name'] ?? null,
                'mobile_no'       => $data['mobile_no'],
                'email'           => $data['email'] ?? null,
                'city'            => $data['city'] ?? null,
                'enquiry_type_id' => $enquiryType->id,
                'lead_source_id'  => $leadSource->id,
                'vehicle_model_id' => $vehicleModelId,
                'created_by'      => $this->createdBy,
                'status'          => 'active',
            ]);

            // Store imported lead date as record creation date.
            $lead->timestamps = false;
            $lead->created_at = $leadDate;
            $lead->updated_at = $leadDate;
            $lead->save();
            $inserted++;
        }

        fclose($handle);
        $handle = null;
        $message = "Import completed. Processed {$totalProcessed} rows. Inserted {$inserted} leads.";
        if ($duplicates > 0) {
            $message .= " Skipped {$duplicates} duplicate rows.";
        }
        if ($invalid > 0) {
            $message .= " Skipped {$invalid} invalid rows.";
        }

        $this->log('info', $message, [
            'event' => 'import_completed',
            'delimiter' => $delimiter,
            'inserted' => $inserted,
            'duplicates' => $duplicates,
            'invalid' => $invalid,
            'total_processed' => $totalProcessed,
        ]);
        $this->markCompleted($totalProcessed, $inserted, $duplicates, $invalid);
        } catch (Throwable $e) {
            $this->log('error', 'Lead import failed unexpectedly', [
                'event' => 'import_failed',
                'errors' => [$e->getMessage()],
            ]);
            $this->markFailed($e->getMessage());
            throw $e;
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }
    }

    /**
     * Prefer comma; if the first non-empty line has more semicolons than commas (common Excel EU export), use ';'.
     *
     * @param  resource  $handle
     */
    private function detectLeadImportCsvDelimiter($handle): string
    {
        while (($line = fgets($handle)) !== false) {
            if (trim($line) === '') {
                continue;
            }
            $semicolons = substr_count($line, ';');
            $commas = substr_count($line, ',');

            return $semicolons > $commas ? ';' : ',';
        }

        return ',';
    }

    /**
     * Trim cells, strip UTF-8 BOM from the first column, and drop trailing empty columns
     * so exports from Excel and "UTF-8 with BOM" still match the expected template header.
     *
     * @param  array<int, string|null>  $header
     * @return array<int, string>
     */
    private function normalizeLeadImportHeaderRow(array $header): array
    {
        $header = array_map(static fn ($v) => trim((string) $v), $header);

        if ($header !== [] && ($header[0] ?? '') !== '') {
            $first = $header[0];
            if (str_starts_with($first, "\xEF\xBB\xBF")) {
                $first = substr($first, 3);
            }
            $first = preg_replace('/^\x{FEFF}/u', '', $first) ?? $first;
            $header[0] = trim($first);
        }

        while ($header !== [] && ($header[array_key_last($header)] ?? '') === '') {
            array_pop($header);
        }

        return array_values($header);
    }

    private function log(string $level, string $message, array $data = []): void
    {
        CustomLog::create([
            'context' => 'lead_import',
            'level' => $level,
            'message' => $message,
            'data' => array_merge(['batch_id' => $this->batchId], $data),
        ]);
    }

    private function markCompleted(int $totalRows, int $inserted, int $duplicates, int $invalid): void
    {
        if (!$this->batch) {
            return;
        }

        $this->batch->update([
            'status' => 'completed',
            'total_rows' => $totalRows,
            'inserted' => $inserted,
            'duplicates' => $duplicates,
            'invalid' => $invalid,
            'completed_at' => now(),
            'error_message' => null,
        ]);
    }

    private function markFailed(string $errorMessage): void
    {
        if (!$this->batch) {
            return;
        }

        $this->batch->update([
            'status' => 'failed',
            'completed_at' => now(),
            'error_message' => $errorMessage,
        ]);
    }
}

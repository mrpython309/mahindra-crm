<?php

namespace App\Jobs;

use App\Models\CustomLog;
use App\Models\Lead;
use App\Models\StatusSource;
use App\Models\VehicleModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProcessUserDataImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $path;
    protected string $batchId;
    protected int $statusSourceId;
    protected int $createdBy;

    public function __construct(string $path, string $batchId, int $statusSourceId, int $createdBy)
    {
        $this->path = $path;
        $this->batchId = $batchId;
        $this->statusSourceId = $statusSourceId;
        $this->createdBy = $createdBy;
    }

    public function handle(): void
    {
        $statusSource = StatusSource::find($this->statusSourceId);
        $normalizedResponse = $statusSource ? strtolower(trim((string) $statusSource->response)) : '';
        $normalizedResponse = preg_replace('/[\s_-]+/', ' ', $normalizedResponse ?? '');
        $isTestDriveScheduled = $statusSource
            && str_contains($normalizedResponse, 'test drive')
            && str_contains($normalizedResponse, 'scheduled');

        $fullPath = Storage::path($this->path);

        if (!is_readable($fullPath)) {
            CustomLog::create([
                'context' => 'user_data_import',
                'level'   => 'error',
                'message' => 'Unable to read uploaded file for user data import',
                'data'    => ['batch_id' => $this->batchId, 'path' => $this->path],
            ]);
            return;
        }

        $handle = fopen($fullPath, 'r');
        if ($handle === false) {
            CustomLog::create([
                'context' => 'user_data_import',
                'level'   => 'error',
                'message' => 'Unable to open uploaded file for user data import',
                'data'    => ['batch_id' => $this->batchId, 'path' => $this->path],
            ]);
            return;
        }

        $expectedHeader = ['first_name', 'last_name', 'mobile_no', 'vehicle_model'];

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
                'context' => 'user_data_import',
                'level'   => 'error',
                'message' => 'The uploaded file is empty or has no header row.',
                'data'    => ['batch_id' => $this->batchId, 'path' => $this->path],
            ]);
            return;
        }

        if ($header !== $expectedHeader) {
            fclose($handle);
            CustomLog::create([
                'context' => 'user_data_import',
                'level'   => 'error',
                'message' => 'Header mismatch. Please use the provided sample CSV template.',
                'data'    => [
                    'batch_id'        => $this->batchId,
                    'path'            => $this->path,
                    'received_header' => $header,
                    'expected_header' => $expectedHeader,
                ],
            ]);
            return;
        }

        $rowNumber = 0;
        $updated   = 0;
        $created   = 0;
        $invalid   = 0;

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $rowNumber++;

            if ($row === [null] || count(array_filter($row, fn($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            if (count($row) < count($expectedHeader)) {
                $row = array_pad($row, count($expectedHeader), null);
            }
            $row  = array_slice($row, 0, count($expectedHeader));
            $data = array_combine($expectedHeader, $row);
            $data = array_map(fn($v) => is_string($v) ? trim($v) : $v, $data);

            $validator = Validator::make($data, [
                'first_name'    => 'required|string|max:255',
                'last_name'     => 'nullable|string|max:255',
                'mobile_no'     => 'required|string|max:20',
                'vehicle_model' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                $invalid++;
                CustomLog::create([
                    'context' => 'user_data_import',
                    'level'   => 'warning',
                    'message' => 'Skipped invalid row during user data import',
                    'data'    => [
                        'batch_id'   => $this->batchId,
                        'row_number' => $rowNumber,
                        'row'        => $data,
                        'errors'     => $validator->errors()->all(),
                    ],
                ]);
                continue;
            }

            // Resolve vehicle_model name → ID (nullable)
            $vehicleModelId = null;
            if (!empty($data['vehicle_model'])) {
                $vehicleModel = VehicleModel::whereRaw('LOWER(name) = ?', [strtolower($data['vehicle_model'])])->first();
                if (!$vehicleModel) {
                    $invalid++;
                    CustomLog::create([
                        'context' => 'user_data_import',
                        'level'   => 'warning',
                        'message' => 'Skipped row: vehicle model not found',
                        'data'    => [
                            'batch_id'   => $this->batchId,
                            'row_number' => $rowNumber,
                            'row'        => $data,
                            'errors'     => ["Vehicle model '{$data['vehicle_model']}' not found. Use a valid vehicle model name or leave blank."],
                        ],
                    ]);
                    continue;
                }
                $vehicleModelId = $vehicleModel->id;
            }

            // Match existing lead by mobile_no + vehicle_model_id (if provided)
            $query = Lead::where('mobile_no', $data['mobile_no']);
            if ($vehicleModelId !== null) {
                $query->where('vehicle_model_id', $vehicleModelId);
            }
            $lead = $query->first();

            if ($lead) {
                if ($isTestDriveScheduled) {
                    $invalid++;
                    CustomLog::create([
                        'context' => 'user_data_import',
                        'level'   => 'warning',
                        'message' => 'Skipped row: Test Drive Scheduled requires Test Drive Date & Time',
                        'data'    => [
                            'batch_id'   => $this->batchId,
                            'row_number' => $rowNumber,
                            'row'        => $data,
                            'errors'     => ['Test Drive Scheduled cannot be set through this import because Test Drive Date & Time is required.'],
                        ],
                    ]);
                    continue;
                }

                $lead->update(['lead_response_id' => $this->statusSourceId]);
                $updated++;
                CustomLog::create([
                    'context' => 'user_data_import',
                    'level'   => 'info',
                    'message' => 'Updated lead response for existing lead',
                    'data'    => [
                        'batch_id'         => $this->batchId,
                        'row_number'       => $rowNumber,
                        'lead_id'          => $lead->id,
                        'mobile_no'        => $data['mobile_no'],
                        'status_source_id' => $this->statusSourceId,
                    ],
                ]);
            } else {
                if ($isTestDriveScheduled) {
                    $invalid++;
                    CustomLog::create([
                        'context' => 'user_data_import',
                        'level'   => 'warning',
                        'message' => 'Skipped row: Test Drive Scheduled requires Test Drive Date & Time',
                        'data'    => [
                            'batch_id'   => $this->batchId,
                            'row_number' => $rowNumber,
                            'row'        => $data,
                            'errors'     => ['Test Drive Scheduled cannot be set through this import because Test Drive Date & Time is required.'],
                        ],
                    ]);
                    continue;
                }

                Lead::create([
                    'first_name'       => $data['first_name'],
                    'last_name'        => $data['last_name'] ?? null,
                    'mobile_no'        => $data['mobile_no'],
                    'vehicle_model_id' => $vehicleModelId,
                    'lead_response_id' => $this->statusSourceId,
                    'created_by'       => $this->createdBy,
                    'status'           => 'active',
                ]);
                $created++;
                CustomLog::create([
                    'context' => 'user_data_import',
                    'level'   => 'info',
                    'message' => 'Created new lead from user data import',
                    'data'    => [
                        'batch_id'         => $this->batchId,
                        'row_number'       => $rowNumber,
                        'mobile_no'        => $data['mobile_no'],
                        'status_source_id' => $this->statusSourceId,
                    ],
                ]);
            }
        }

        fclose($handle);

        $totalProcessed = $updated + $created + $invalid;
        $message = "User data import completed. Processed {$totalProcessed} rows. Updated {$updated} leads. Created {$created} new leads.";
        if ($invalid > 0) {
            $message .= " Skipped {$invalid} invalid rows.";
        }

        CustomLog::create([
            'context' => 'user_data_import',
            'level'   => 'info',
            'message' => $message,
            'data'    => [
                'batch_id'        => $this->batchId,
                'updated'         => $updated,
                'created'         => $created,
                'invalid'         => $invalid,
                'total_processed' => $totalProcessed,
            ],
        ]);
    }
}

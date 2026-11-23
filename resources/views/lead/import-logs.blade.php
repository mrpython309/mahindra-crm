@extends('layouts/contentNavbarLayout')

@section('title', 'Lead import logs')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    <a href="{{ route('leads.index') }}" class="text-muted">Leads</a>
    <span class="text-muted fw-normal">/</span>
    Lead import logs
</h4>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="{{ route('leads.import.logs') }}" class="row g-2 align-items-end">
            <div class="col-md-4 col-lg-3">
                <label class="form-label mb-0" for="batchFilter">Filter by batch ID</label>
                <input id="batchFilter" type="text" name="batch" value="{{ $batchFilter }}" class="form-control form-control-sm" placeholder="e.g. lead_import_67d2...">
            </div>
            <div class="col-md-2 col-lg-2">
                <label class="form-label mb-0" for="statusFilter">Status</label>
                <select id="statusFilter" name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach (['queued' => 'Queued', 'processing' => 'Processing', 'completed' => 'Completed', 'failed' => 'Failed'] as $key => $label)
                        <option value="{{ $key }}" @selected($statusFilter === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="form-label mb-0" for="dateFromFilter">From</label>
                <input id="dateFromFilter" type="date" name="date_from" value="{{ $dateFrom }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="form-label mb-0" for="dateToFilter">To</label>
                <input id="dateToFilter" type="date" name="date_to" value="{{ $dateTo }}" class="form-control form-control-sm">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
            </div>
            @if($batchFilter !== '' || $statusFilter !== '' || $dateFrom !== '' || $dateTo !== '')
                <div class="col-auto">
                    <a href="{{ route('leads.import.logs') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                </div>
            @endif
        </form>
        <p class="text-muted small mb-0 mt-2">Each upload creates one batch summary with row-level logs nested under it.</p>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Lead import batches</h5>
        <a href="{{ route('leads.index') }}" class="btn btn-sm btn-outline-secondary">Back to leads</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width: 17rem;">Batch ID</th>
                    <th style="width: 11rem;">Uploaded</th>
                    <th style="width: 10rem;">By</th>
                    <th style="width: 8rem;">Status</th>
                    <th style="width: 14rem;">Counts</th>
                    <th style="width: 9rem;">Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($batches as $batch)
                    @php
                        $statusClass = match ($batch->status) {
                            'completed' => 'success',
                            'failed' => 'danger',
                            'processing' => 'warning',
                            default => 'secondary',
                        };
                        $batchLogs = $logsByBatch->get($batch->batch_id, collect());
                        $detailId = 'batch-details-' . $batch->id;
                    @endphp
                    <tr>
                        <td class="small">
                            <div class="fw-semibold">{{ $batch->batch_id }}</div>
                            @if($batch->file_path)
                                <div class="text-muted">File: {{ $batch->file_path }}</div>
                            @endif
                        </td>
                        <td class="text-nowrap small">
                            <div>{{ $batch->created_at?->format('Y-m-d H:i:s') }}</div>
                            @if($batch->started_at)
                                <div class="text-muted">Start: {{ $batch->started_at->format('H:i:s') }}</div>
                            @endif
                            @if($batch->completed_at)
                                <div class="text-muted">End: {{ $batch->completed_at->format('H:i:s') }}</div>
                            @endif
                        </td>
                        <td class="small">{{ $batch->creator?->name ?? 'System' }}</td>
                        <td>
                            <span class="badge bg-label-{{ $statusClass }}{{ $batch->status === 'processing' ? ' text-dark' : '' }}">
                                {{ ucfirst($batch->status) }}
                            </span>
                        </td>
                        <td class="small">
                            <div>Total: {{ $batch->total_rows }}</div>
                            <div class="text-success">Inserted: {{ $batch->inserted }}</div>
                            <div class="text-warning">Duplicates: {{ $batch->duplicates }}</div>
                            <div class="text-danger">Invalid: {{ $batch->invalid }}</div>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $detailId }}" aria-expanded="false">
                                Show logs
                            </button>
                        </td>
                    </tr>
                    <tr class="collapse bg-light" id="{{ $detailId }}">
                        <td colspan="6">
                            <div class="p-3">
                                @if($batch->error_message)
                                    <div class="alert alert-danger py-2 px-3 mb-3">
                                        <strong>Batch error:</strong> {{ $batch->error_message }}
                                    </div>
                                @endif

                                @if($batchLogs->isEmpty())
                                    <p class="text-muted small mb-0">No row-level logs for this batch.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0 bg-white">
                                            <thead>
                                                <tr>
                                                    <th style="width: 11rem;">When</th>
                                                    <th style="width: 6rem;">Level</th>
                                                    <th style="width: 10rem;">Event</th>
                                                    <th style="width: 6rem;">Row</th>
                                                    <th>Message / Errors</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($batchLogs as $log)
                                                    @php
                                                        $levelClass = match ($log->level) {
                                                            'error' => 'danger',
                                                            'warning' => 'warning',
                                                            default => 'info',
                                                        };
                                                        $errors = data_get($log->data, 'errors', []);
                                                        $event = data_get($log->data, 'event', 'n/a');
                                                        $rowNumber = data_get($log->data, 'row_number');
                                                    @endphp
                                                    <tr>
                                                        <td class="text-nowrap small">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                                        <td><span class="badge bg-label-{{ $levelClass }}{{ $log->level === 'warning' ? ' text-dark' : '' }}">{{ $log->level }}</span></td>
                                                        <td class="small">{{ $event }}</td>
                                                        <td class="small">{{ $rowNumber ?? ' - ' }}</td>
                                                        <td class="small">
                                                            <div>{{ $log->message }}</div>
                                                            @if(is_array($errors) && count($errors) > 0)
                                                                <ul class="mb-0 mt-1 ps-3 text-danger">
                                                                    @foreach($errors as $error)
                                                                        <li>{{ $error }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No lead import batches found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($batches->hasPages())
        <div class="card-body pt-0">
            {{ $batches->links() }}
        </div>
    @endif
</div>

@if($legacyLogs->isNotEmpty())
    <div class="card mt-4">
        <div class="card-header">
            <h6 class="mb-0">Legacy log entries (without batch ID)</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 11rem;">When</th>
                        <th style="width: 6rem;">Level</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($legacyLogs as $log)
                        @php
                            $levelClass = match ($log->level) {
                                'error' => 'danger',
                                'warning' => 'warning',
                                default => 'info',
                            };
                        @endphp
                        <tr>
                            <td class="text-nowrap small">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td><span class="badge bg-label-{{ $levelClass }}{{ $log->level === 'warning' ? ' text-dark' : '' }}">{{ $log->level }}</span></td>
                            <td class="small">{{ $log->message }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection

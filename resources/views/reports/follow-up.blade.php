@extends('layouts/contentNavbarLayout')

@section('title', 'Follow-up Due Report')

@section('vendor-style')
@vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

@section('vendor-script')
@vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between py-3 breadcrumb-wrapper mb-4">
    <h4 class="mb-0">Follow-up Due Report</h4>
    @can('reports:index')
    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bx bx-arrow-back me-1"></i> All Reports
    </a>
    @else
    <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm">
        <i class="bx bx-arrow-back me-1"></i> Back
    </a>
    @endcan
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.follow-up') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Period</label>
                <select name="period" class="form-select">
                    <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Due Today</option>
                    <option value="overdue" {{ $period === 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="week" {{ $period === 'week' ? 'selected' : '' }}>This Week</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Company</label>
                <select name="company_id" class="form-select">
                    <option value="">All Companies</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Assigned To</label>
                <select name="assigned_to" class="form-select">
                    <option value="">All Employees</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bx bx-filter-alt me-1"></i> Apply
                </button>
                <a href="{{ route('reports.follow-up') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Stat Cards --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <a href="{{ route('reports.follow-up', array_merge(request()->query(), ['period' => 'overdue'])) }}" class="text-decoration-none">
            <div class="card h-100 {{ $period === 'overdue' ? 'border border-danger' : '' }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="avatar">
                        <span class="avatar-initial rounded-2 bg-label-danger">
                            <i class="icon-base bx bx-time icon-lg"></i>
                        </span>
                    </div>
                    <div>
                        <small class="text-muted d-block">Overdue</small>
                        <h4 class="mb-0 text-danger">{{ $overdueCount }}</h4>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('reports.follow-up', array_merge(request()->query(), ['period' => 'today'])) }}" class="text-decoration-none">
            <div class="card h-100 {{ $period === 'today' ? 'border border-warning' : '' }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="avatar">
                        <span class="avatar-initial rounded-2 bg-label-warning">
                            <i class="icon-base bx bx-calendar-check icon-lg"></i>
                        </span>
                    </div>
                    <div>
                        <small class="text-muted d-block">Due Today</small>
                        <h4 class="mb-0 text-warning">{{ $todayCount }}</h4>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('reports.follow-up', array_merge(request()->query(), ['period' => 'week'])) }}" class="text-decoration-none">
            <div class="card h-100 {{ $period === 'week' ? 'border border-success' : '' }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="avatar">
                        <span class="avatar-initial rounded-2 bg-label-success">
                            <i class="icon-base bx bx-calendar icon-lg"></i>
                        </span>
                    </div>
                    <div>
                        <small class="text-muted d-block">This Week</small>
                        <h4 class="mb-0 text-success">{{ $thisWeekCount }}</h4>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

{{-- Bar Chart --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Follow-up Overview</h5>
    </div>
    <div class="card-body">
        <div id="followUpBarChart"></div>
    </div>
</div>

{{-- Leads Table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            @if($period === 'overdue') Overdue Follow-ups
            @elseif($period === 'week') This Week's Follow-ups
            @else Today's Follow-ups
            @endif
            <span class="badge bg-label-primary ms-2">{{ $leads->total() }}</span>
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Lead Name</th>
                        <th>Mobile</th>
                        <th>Vehicle Model</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Follow-up Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leads as $lead)
                    <tr>
                        <td>{{ $loop->iteration + ($leads->currentPage() - 1) * $leads->perPage() }}</td>
                        <td>{{ $lead->first_name }} {{ $lead->last_name }}</td>
                        <td>{{ $lead->mobile_no }}</td>
                        <td>{{ $lead->vehicleModel?->name ?? '-' }}</td>
                        <td>
                            @if($lead->leadStatus)
                                <span class="badge bg-label-secondary">{{ $lead->leadStatus->name }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $lead->assignedUser?->name ?? '-' }}</td>
                        <td>
                            <span class="{{ $lead->next_follow_up < now() ? 'text-danger fw-semibold' : '' }}">
                                {{ $lead->next_follow_up?->format('d M Y') ?? '-' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-show"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No follow-ups found for the selected period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($leads->hasPages())
    <div class="card-footer">
        {{ $leads->appends(request()->query())->links() }}
    </div>
    @endif
</div>

@push('scripts')
<script>
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const textColor = isDark ? '#b4bdc6' : '#697a8d';
    const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';

    const barData = @json($barData);
    new ApexCharts(document.getElementById('followUpBarChart'), {
        chart: { type: 'bar', height: 250, toolbar: { show: false } },
        series: [{ name: 'Leads', data: barData.map(d => d.value) }],
        xaxis: { categories: barData.map(d => d.label), labels: { style: { colors: textColor } } },
        yaxis: { labels: { style: { colors: textColor } } },
        grid: { borderColor: gridColor },
        plotOptions: { bar: { borderRadius: 4, columnWidth: '35%' } },
        dataLabels: { enabled: true },
        colors: ['#ff3e1d', '#ffab00', '#71dd37']
    }).render();
</script>
@endpush
@endsection

@extends('layouts/contentNavbarLayout')

@section('title', 'Likely Purchase Report')

@section('vendor-style')
@vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

@section('vendor-script')
@vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between py-3 breadcrumb-wrapper mb-4">
    <h4 class="mb-0">Likely Purchase Report</h4>
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
        <form method="GET" action="{{ route('reports.likely-purchase') }}" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
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
            <div class="col-md-2 d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bx bx-filter-alt me-1"></i> Apply
                </button>
                <a href="{{ route('reports.likely-purchase') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Stat Card --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="avatar">
                    <span class="avatar-initial rounded-2 bg-label-primary">
                        <i class="icon-base bx bx-trending-up icon-lg"></i>
                    </span>
                </div>
                <div>
                    <small class="text-muted d-block">Total Leads with Purchase Timeline</small>
                    <h4 class="mb-0">{{ $totalLeads }}</h4>
                </div>
            </div>
        </div>
    </div>
    @foreach($byLikely as $row)
    <div class="col-md-{{ $byLikely->count() > 0 ? (int)(8 / max($byLikely->count(), 1)) : 4 }}">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="avatar">
                    <span class="avatar-initial rounded-2 {{ $loop->first ? 'bg-label-danger' : ($loop->last ? 'bg-label-success' : 'bg-label-warning') }}">
                        <i class="icon-base bx bx-time-five icon-lg"></i>
                    </span>
                </div>
                <div>
                    <small class="text-muted d-block">{{ $row['label'] }}</small>
                    <h4 class="mb-0">{{ $row['value'] }}</h4>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Charts --}}
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Purchase Timeline Distribution</h5>
            </div>
            <div class="card-body">
                <div id="likelyDonutChart"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Likely Purchase by Vehicle Model</h5>
            </div>
            <div class="card-body">
                <div id="modelLikelyChart"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const textColor = isDark ? '#b4bdc6' : '#697a8d';
    const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';

    // Likely purchase donut
    const likelyData = @json($byLikely);
    new ApexCharts(document.getElementById('likelyDonutChart'), {
        chart: { type: 'donut', height: 320 },
        series: likelyData.map(d => d.value),
        labels: likelyData.map(d => d.label),
        colors: ['#ff3e1d', '#ffab00', '#71dd37'],
        legend: { position: 'bottom', labels: { colors: textColor } },
        dataLabels: { enabled: true },
        responsive: [{ breakpoint: 480, options: { chart: { height: 260 } } }]
    }).render();

    // Model vs likely purchase stacked bar
    const modelNames = @json($modelNames);
    const modelSeries = @json($modelSeries);
    new ApexCharts(document.getElementById('modelLikelyChart'), {
        chart: { type: 'bar', height: 320, stacked: true, toolbar: { show: false } },
        series: modelSeries,
        xaxis: { categories: modelNames, labels: { style: { colors: textColor }, rotate: -30 } },
        yaxis: { labels: { style: { colors: textColor } } },
        grid: { borderColor: gridColor },
        plotOptions: { bar: { borderRadius: 0, columnWidth: '55%' } },
        dataLabels: { enabled: false },
        colors: ['#ff3e1d', '#ffab00', '#71dd37'],
        legend: { position: 'top', labels: { colors: textColor } }
    }).render();
</script>
@endpush
@endsection

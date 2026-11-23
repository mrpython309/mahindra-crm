@extends('layouts/contentNavbarLayout')

@section('title', 'Lead Summary Report')

@section('vendor-style')
@vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

@section('vendor-script')
@vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between py-3 breadcrumb-wrapper mb-4">
    <h4 class="mb-0">Lead Summary Report</h4>
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
        <form method="GET" action="{{ route('reports.lead-summary') }}" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Company</label>
                <select name="company_id" class="form-select">
                    <option value="">All Companies</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Lead Type</label>
                <select name="lead_type_id" class="form-select">
                    <option value="">All Types</option>
                    @foreach($leadTypes as $type)
                        <option value="{{ $type->id }}" {{ request('lead_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Lead Source</label>
                <select name="lead_source_id" class="form-select">
                    <option value="">All Sources</option>
                    @foreach($leadSources as $source)
                        <option value="{{ $source->id }}" {{ request('lead_source_id') == $source->id ? 'selected' : '' }}>{{ $source->campaign }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Trend By</label>
                <select name="trend" class="form-select">
                    <option value="daily" {{ request('trend','daily') === 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="weekly" {{ request('trend') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ request('trend') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                </select>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bx bx-filter-alt me-1"></i> Apply Filters
                </button>
                <a href="{{ route('reports.lead-summary') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Stat Cards --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="avatar">
                    <span class="avatar-initial rounded-2 bg-label-primary">
                        <i class="icon-base bx bx-user-plus icon-lg"></i>
                    </span>
                </div>
                <div>
                    <small class="text-muted d-block">Total Leads</small>
                    <h4 class="mb-0">{{ $totalLeads }}</h4>
                </div>
            </div>
        </div>
    </div>
    @foreach($byStatus->take(3) as $stat)
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="avatar">
                    <span class="avatar-initial rounded-2 bg-label-info">
                        <i class="icon-base bx bx-check-circle icon-lg"></i>
                    </span>
                </div>
                <div>
                    <small class="text-muted d-block">{{ $stat['label'] }}</small>
                    <h4 class="mb-0">{{ $stat['value'] }}</h4>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Charts Row 1 --}}
<div class="row g-4 mb-4">
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Leads by Status</h5>
            </div>
            <div class="card-body">
                <div id="statusDonutChart"></div>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Leads by Source</h5>
            </div>
            <div class="card-body">
                <div id="sourceBarChart"></div>
            </div>
        </div>
    </div>
</div>

{{-- Trend Chart --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Lead Inflow Trend</h5>
    </div>
    <div class="card-body">
        <div id="trendLineChart"></div>
    </div>
</div>

{{-- Lead Type Chart --}}
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Leads by Type</h5>
    </div>
    <div class="card-body">
        <div id="typeBarChart"></div>
    </div>
</div>

@push('scripts')
<script>
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const textColor = isDark ? '#b4bdc6' : '#697a8d';
    const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';

    // Status Donut
    const statusData = @json($byStatus);
    new ApexCharts(document.getElementById('statusDonutChart'), {
        chart: { type: 'donut', height: 300 },
        series: statusData.map(d => d.value),
        labels: statusData.map(d => d.label),
        legend: { position: 'bottom', labels: { colors: textColor } },
        dataLabels: { enabled: true },
        responsive: [{ breakpoint: 480, options: { chart: { height: 250 } } }]
    }).render();

    // Source Bar
    const sourceData = @json($bySource);
    new ApexCharts(document.getElementById('sourceBarChart'), {
        chart: { type: 'bar', height: 300, toolbar: { show: false } },
        series: [{ name: 'Leads', data: sourceData.map(d => d.value) }],
        xaxis: { categories: sourceData.map(d => d.label), labels: { style: { colors: textColor } } },
        yaxis: { labels: { style: { colors: textColor } } },
        grid: { borderColor: gridColor },
        plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
        dataLabels: { enabled: false },
        colors: ['#696cff']
    }).render();

    // Trend Line
    const trendData = @json($trend);
    new ApexCharts(document.getElementById('trendLineChart'), {
        chart: { type: 'area', height: 280, toolbar: { show: false }, zoom: { enabled: false } },
        series: [{ name: 'Leads', data: trendData.map(d => d.total) }],
        xaxis: { categories: trendData.map(d => d.period), labels: { style: { colors: textColor }, rotate: -30 } },
        yaxis: { labels: { style: { colors: textColor } } },
        grid: { borderColor: gridColor },
        stroke: { curve: 'smooth', width: 2 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05 } },
        dataLabels: { enabled: false },
        colors: ['#696cff']
    }).render();

    // Type Bar
    const typeData = @json($byType);
    new ApexCharts(document.getElementById('typeBarChart'), {
        chart: { type: 'bar', height: 250, toolbar: { show: false } },
        series: [{ name: 'Leads', data: typeData.map(d => d.value) }],
        xaxis: { categories: typeData.map(d => d.label), labels: { style: { colors: textColor } } },
        yaxis: { labels: { style: { colors: textColor } } },
        grid: { borderColor: gridColor },
        plotOptions: { bar: { borderRadius: 4, columnWidth: '40%' } },
        dataLabels: { enabled: false },
        colors: ['#03c3ec']
    }).render();
</script>
@endpush
@endsection

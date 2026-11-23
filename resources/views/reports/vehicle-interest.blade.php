@extends('layouts/contentNavbarLayout')

@section('title', 'Vehicle Model Interest Report')

@section('vendor-style')
@vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

@section('vendor-script')
@vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between py-3 breadcrumb-wrapper mb-4">
    <h4 class="mb-0">Vehicle Model Interest Report</h4>
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
        <form method="GET" action="{{ route('reports.vehicle-interest') }}" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Segment</label>
                <select name="segment_id" class="form-select">
                    <option value="">All Segments</option>
                    @foreach($segments as $segment)
                        <option value="{{ $segment->id }}" {{ request('segment_id') == $segment->id ? 'selected' : '' }}>{{ $segment->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bx bx-filter-alt me-1"></i> Apply
                </button>
                <a href="{{ route('reports.vehicle-interest') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Charts Row 1 --}}
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Top 10 Most Enquired Vehicle Models</h5>
            </div>
            <div class="card-body">
                <div id="topModelsChart"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Leads by Segment</h5>
            </div>
            <div class="card-body">
                <div id="segmentDonutChart"></div>
            </div>
        </div>
    </div>
</div>

{{-- Stacked Chart --}}
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Model vs Fuel Type Breakdown</h5>
    </div>
    <div class="card-body">
        <div id="modelFuelStackedChart"></div>
    </div>
</div>

@push('scripts')
<script>
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const textColor = isDark ? '#b4bdc6' : '#697a8d';
    const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';

    // Top models bar
    const modelData = @json($byModel);
    new ApexCharts(document.getElementById('topModelsChart'), {
        chart: { type: 'bar', height: 320, toolbar: { show: false } },
        series: [{ name: 'Enquiries', data: modelData.map(d => d.value) }],
        xaxis: { categories: modelData.map(d => d.label), labels: { style: { colors: textColor } } },
        yaxis: { labels: { style: { colors: textColor } } },
        grid: { borderColor: gridColor },
        plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
        dataLabels: { enabled: false },
        colors: ['#696cff']
    }).render();

    // Segment donut
    const segmentData = @json($bySegment);
    new ApexCharts(document.getElementById('segmentDonutChart'), {
        chart: { type: 'donut', height: 320 },
        series: segmentData.map(d => d.value),
        labels: segmentData.map(d => d.label),
        legend: { position: 'bottom', labels: { colors: textColor } },
        dataLabels: { enabled: true },
        responsive: [{ breakpoint: 480, options: { chart: { height: 250 } } }]
    }).render();

    // Model vs fuel stacked
    const stackedModels = @json($stackedModels);
    const stackedSeries = @json($stackedSeries);
    new ApexCharts(document.getElementById('modelFuelStackedChart'), {
        chart: { type: 'bar', height: 320, stacked: true, toolbar: { show: false } },
        series: stackedSeries,
        xaxis: { categories: stackedModels, labels: { style: { colors: textColor } } },
        yaxis: { labels: { style: { colors: textColor } } },
        grid: { borderColor: gridColor },
        plotOptions: { bar: { borderRadius: 0, columnWidth: '50%' } },
        dataLabels: { enabled: false },
        legend: { position: 'top', labels: { colors: textColor } }
    }).render();
</script>
@endpush
@endsection

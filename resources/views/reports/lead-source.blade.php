@extends('layouts/contentNavbarLayout')

@section('title', 'Lead Source Performance Report')

@section('vendor-style')
@vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

@section('vendor-script')
@vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between py-3 breadcrumb-wrapper mb-4">
    <h4 class="mb-0">Lead Source Performance Report</h4>
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
        <form method="GET" action="{{ route('reports.lead-source') }}" class="row g-3 align-items-end">
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
                <label class="form-label">Lead Type</label>
                <select name="lead_type_id" class="form-select">
                    <option value="">All Types</option>
                    @foreach($leadTypes as $type)
                        <option value="{{ $type->id }}" {{ request('lead_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bx bx-filter-alt me-1"></i> Apply
                </button>
                <a href="{{ route('reports.lead-source') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Charts --}}
<div class="row g-4 mb-4">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Leads by Source</h5>
            </div>
            <div class="card-body">
                <div id="sourceDonutChart"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Source vs Lead Status (Conversion Funnel)</h5>
            </div>
            <div class="card-body">
                <div id="sourceFunnelChart"></div>
            </div>
        </div>
    </div>
</div>

{{-- Summary Table --}}
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Source Summary</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Lead Source</th>
                        <th>Total Leads</th>
                        <th>Share %</th>
                    </tr>
                </thead>
                <tbody>
                    @php $total = $bySource->sum('value'); @endphp
                    @forelse($bySource as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $row['label'] }}</strong></td>
                        <td>{{ $row['value'] }}</td>
                        <td>
                            @if($total > 0)
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:6px;">
                                        <div class="progress-bar" style="width:{{ round(($row['value']/$total)*100) }}%"></div>
                                    </div>
                                    <small>{{ round(($row['value']/$total)*100) }}%</small>
                                </div>
                            @else
                                0%
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No data found for the selected filters.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const textColor = isDark ? '#b4bdc6' : '#697a8d';
    const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';

    // Source donut
    const sourceData = @json($bySource);
    new ApexCharts(document.getElementById('sourceDonutChart'), {
        chart: { type: 'donut', height: 340 },
        series: sourceData.map(d => d.value),
        labels: sourceData.map(d => d.label),
        legend: { position: 'bottom', labels: { colors: textColor } },
        dataLabels: { enabled: true },
        responsive: [{ breakpoint: 480, options: { chart: { height: 260 } } }]
    }).render();

    // Source vs status stacked bar
    const sources = @json($sources);
    const crossSeries = @json($crossSeries);
    new ApexCharts(document.getElementById('sourceFunnelChart'), {
        chart: { type: 'bar', height: 340, stacked: true, toolbar: { show: false } },
        series: crossSeries,
        xaxis: { categories: sources, labels: { style: { colors: textColor }, rotate: -30 } },
        yaxis: { labels: { style: { colors: textColor } } },
        grid: { borderColor: gridColor },
        plotOptions: { bar: { borderRadius: 0, columnWidth: '55%' } },
        dataLabels: { enabled: false },
        legend: { position: 'top', labels: { colors: textColor } }
    }).render();
</script>
@endpush
@endsection

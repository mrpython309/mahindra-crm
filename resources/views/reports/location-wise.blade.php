@extends('layouts/contentNavbarLayout')

@section('title', 'Dealer Location-wise Report')

@section('vendor-style')
@vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

@section('vendor-script')
@vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between py-3 breadcrumb-wrapper mb-4">
    <h4 class="mb-0">Dealer Location-wise Report</h4>
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
        <form method="GET" action="{{ route('reports.location-wise') }}" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Company</label>
                <select name="company_id" class="form-select">
                    <option value="">All Companies</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bx bx-filter-alt me-1"></i> Apply
                </button>
                <a href="{{ route('reports.location-wise') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Charts --}}
<div class="row g-4 mb-4">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Leads per Dealer Location</h5>
            </div>
            <div class="card-body">
                <div id="locationBarChart"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Location vs Lead Status</h5>
            </div>
            <div class="card-body">
                <div id="locationStackedChart"></div>
            </div>
        </div>
    </div>
</div>

{{-- Summary Table --}}
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Location Summary</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Location</th>
                        <th>Total Leads</th>
                        <th>Share %</th>
                    </tr>
                </thead>
                <tbody>
                    @php $total = $byLocation->sum('value'); @endphp
                    @forelse($byLocation as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $row['label'] }}</strong></td>
                        <td>{{ $row['value'] }}</td>
                        <td>
                            @if($total > 0)
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:6px;">
                                        <div class="progress-bar bg-info" style="width:{{ round(($row['value']/$total)*100) }}%"></div>
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

    // Location bar
    const locationData = @json($byLocation);
    new ApexCharts(document.getElementById('locationBarChart'), {
        chart: { type: 'bar', height: 340, toolbar: { show: false } },
        series: [{ name: 'Leads', data: locationData.map(d => d.value) }],
        xaxis: { categories: locationData.map(d => d.label), labels: { style: { colors: textColor }, rotate: -30 } },
        yaxis: { labels: { style: { colors: textColor } } },
        grid: { borderColor: gridColor },
        plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
        dataLabels: { enabled: true },
        colors: ['#03c3ec']
    }).render();

    // Location vs status stacked
    const locationNames = @json($locationNames);
    const stackedSeries = @json($stackedSeries);
    new ApexCharts(document.getElementById('locationStackedChart'), {
        chart: { type: 'bar', height: 340, stacked: true, toolbar: { show: false } },
        series: stackedSeries,
        xaxis: { categories: locationNames, labels: { style: { colors: textColor }, rotate: -30 } },
        yaxis: { labels: { style: { colors: textColor } } },
        grid: { borderColor: gridColor },
        plotOptions: { bar: { borderRadius: 0, columnWidth: '55%' } },
        dataLabels: { enabled: false },
        legend: { position: 'top', labels: { colors: textColor } }
    }).render();
</script>
@endpush
@endsection

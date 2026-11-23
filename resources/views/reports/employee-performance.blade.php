@extends('layouts/contentNavbarLayout')

@section('title', 'Employee Performance Report')

@section('vendor-style')
@vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

@section('vendor-script')
@vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between py-3 breadcrumb-wrapper mb-4">
    <h4 class="mb-0">Employee Performance Report</h4>
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
        <form method="GET" action="{{ route('reports.employee-performance') }}" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Role</label>
                <select name="role" class="form-select">
                    <option value="all" {{ $roleFilter === 'all' ? 'selected' : '' }}>All Roles</option>
                    <option value="tele-caller" {{ $roleFilter === 'tele-caller' ? 'selected' : '' }}>Tele-Caller</option>
                    <option value="sales-consultant" {{ $roleFilter === 'sales-consultant' ? 'selected' : '' }}>Sales Consultant</option>
                </select>
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
                <label class="form-label">Location</label>
                <select name="location_id" class="form-select">
                    <option value="">All Locations</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bx bx-filter-alt me-1"></i> Apply
                </button>
                <a href="{{ route('reports.employee-performance') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Charts --}}
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Leads Assigned per Employee</h5>
            </div>
            <div class="card-body">
                <div id="leadsAssignedChart"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Calls Made per Employee</h5>
            </div>
            <div class="card-body">
                <div id="callsMadeChart"></div>
            </div>
        </div>
    </div>
</div>

{{-- Data Table --}}
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Performance Summary</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Employee Name</th>
                        <th>Tele-Caller Leads</th>
                        <th>Sales Leads</th>
                        <th>Total Leads</th>
                        <th>Calls Made</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($performanceData as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $row['name'] }}</strong></td>
                        <td>{{ $row['tele_caller_leads'] }}</td>
                        <td>{{ $row['sales_leads'] }}</td>
                        <td><span class="badge bg-label-primary">{{ $row['tele_caller_leads'] + $row['sales_leads'] }}</span></td>
                        <td>{{ $row['calls_count'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No data found for the selected filters.</td>
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

    const perfData = @json($performanceData);
    const names = perfData.map(d => d.name);

    // Leads assigned (grouped bar: tele-caller vs sales)
    new ApexCharts(document.getElementById('leadsAssignedChart'), {
        chart: { type: 'bar', height: 320, toolbar: { show: false } },
        series: [
            { name: 'Tele-Caller Leads', data: perfData.map(d => d.tele_caller_leads) },
            { name: 'Sales Leads', data: perfData.map(d => d.sales_leads) }
        ],
        xaxis: { categories: names, labels: { style: { colors: textColor } } },
        yaxis: { labels: { style: { colors: textColor } } },
        grid: { borderColor: gridColor },
        plotOptions: { bar: { borderRadius: 4, columnWidth: '55%', grouped: true } },
        dataLabels: { enabled: false },
        colors: ['#696cff', '#03c3ec'],
        legend: { labels: { colors: textColor } }
    }).render();

    // Calls made (horizontal bar)
    new ApexCharts(document.getElementById('callsMadeChart'), {
        chart: { type: 'bar', height: 320, toolbar: { show: false } },
        series: [{ name: 'Calls Made', data: perfData.map(d => d.calls_count) }],
        xaxis: { categories: names, labels: { style: { colors: textColor } } },
        yaxis: { labels: { style: { colors: textColor } } },
        grid: { borderColor: gridColor },
        plotOptions: { bar: { borderRadius: 4, horizontal: true, barHeight: '50%' } },
        dataLabels: { enabled: true },
        colors: ['#71dd37']
    }).render();
</script>
@endpush
@endsection

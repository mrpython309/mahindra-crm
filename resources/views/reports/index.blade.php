@extends('layouts/contentNavbarLayout')

@section('title', 'Reports')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    Reports
</h4>

<div class="row g-4">
    @can('reports:lead-summary')
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('reports.lead-summary') }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm report-card">
                <div class="card-body d-flex align-items-start gap-3 p-4">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded-2 bg-label-primary">
                            <i class="icon-base bx bx-bar-chart-alt-2 icon-lg"></i>
                        </span>
                    </div>
                    <div>
                        <h6 class="mb-1">Lead Summary Report</h6>
                        <small class="text-muted">Total leads by status, source and trend over time</small>
                    </div>
                </div>
            </div>
        </a>
    </div>
    @endcan

    @can('reports:follow-up')
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('reports.follow-up') }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm report-card">
                <div class="card-body d-flex align-items-start gap-3 p-4">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded-2 bg-label-warning">
                            <i class="icon-base bx bx-calendar-check icon-lg"></i>
                        </span>
                    </div>
                    <div>
                        <h6 class="mb-1">Follow-up Due Report</h6>
                        <small class="text-muted">Overdue, today and upcoming follow-up leads</small>
                    </div>
                </div>
            </div>
        </a>
    </div>
    @endcan

    @can('reports:employee-performance')
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('reports.employee-performance') }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm report-card">
                <div class="card-body d-flex align-items-start gap-3 p-4">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded-2 bg-label-success">
                            <i class="icon-base bx bx-user-check icon-lg"></i>
                        </span>
                    </div>
                    <div>
                        <h6 class="mb-1">Employee Performance Report</h6>
                        <small class="text-muted">Leads assigned and calls made per employee</small>
                    </div>
                </div>
            </div>
        </a>
    </div>
    @endcan

    @can('reports:vehicle-interest')
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('reports.vehicle-interest') }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm report-card">
                <div class="card-body d-flex align-items-start gap-3 p-4">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded-2 bg-label-info">
                            <i class="icon-base bx bx-car icon-lg"></i>
                        </span>
                    </div>
                    <div>
                        <h6 class="mb-1">Vehicle Model Interest Report</h6>
                        <small class="text-muted">Top enquired models, segments and fuel types</small>
                    </div>
                </div>
            </div>
        </a>
    </div>
    @endcan

    @can('reports:lead-source')
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('reports.lead-source') }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm report-card">
                <div class="card-body d-flex align-items-start gap-3 p-4">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded-2 bg-label-danger">
                            <i class="icon-base bx bx-radar icon-lg"></i>
                        </span>
                    </div>
                    <div>
                        <h6 class="mb-1">Lead Source Performance Report</h6>
                        <small class="text-muted">Leads and conversion rate per source channel</small>
                    </div>
                </div>
            </div>
        </a>
    </div>
    @endcan

    @can('reports:location-wise')
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('reports.location-wise') }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm report-card">
                <div class="card-body d-flex align-items-start gap-3 p-4">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded-2 bg-label-secondary">
                            <i class="icon-base bx bx-map-pin icon-lg"></i>
                        </span>
                    </div>
                    <div>
                        <h6 class="mb-1">Dealer Location-wise Report</h6>
                        <small class="text-muted">Leads per branch and status breakdown by location</small>
                    </div>
                </div>
            </div>
        </a>
    </div>
    @endcan

    @can('reports:likely-purchase')
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('reports.likely-purchase') }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm report-card">
                <div class="card-body d-flex align-items-start gap-3 p-4">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded-2 bg-label-primary">
                            <i class="icon-base bx bx-trending-up icon-lg"></i>
                        </span>
                    </div>
                    <div>
                        <h6 class="mb-1">Likely Purchase Report</h6>
                        <small class="text-muted">Hot leads segmented by purchase timeline</small>
                    </div>
                </div>
            </div>
        </a>
    </div>
    @endcan
</div>

@cannot('reports:lead-summary')
    @cannot('reports:follow-up')
        @cannot('reports:employee-performance')
            @cannot('reports:vehicle-interest')
                @cannot('reports:lead-source')
                    @cannot('reports:location-wise')
                        @cannot('reports:likely-purchase')
                            <p class="text-muted">You do not have access to any reports. Contact an administrator if you need access.</p>
                        @endcannot
                    @endcannot
                @endcannot
            @endcannot
        @endcannot
    @endcannot
@endcannot

@push('styles')
<style>
    .report-card {
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        cursor: pointer;
    }
    .report-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,.15) !important;
    }
</style>
@endpush
@endsection

@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Analytics')

@section('vendor-style')
@vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

@section('vendor-script')
@vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('page-script')
<script>
  window.dashboardData = {
    leadsMonthly:    @json($leadsMonthly),
    leadsByStatus:   @json($leadsByStatus),
    leadsBySource:   @json($leadsBySource),
    leadsBySegment:  @json($leadsBySegment),
    topVehicleModels: @json($topVehicleModels),
  };
</script>
@vite('resources/assets/js/dashboards-analytics.js')
@endsection

@section('content')

{{-- Row 1: KPI Cards --}}
<div class="row mb-6">
  <div class="col-sm-6 col-xl-3 mb-6 mb-xl-0">
    <div class="card h-100">
      <div class="card-body d-flex align-items-center gap-4">
        <div class="avatar avatar-lg flex-shrink-0">
          <span class="avatar-initial rounded bg-label-primary">
            <i class="icon-base bx bx-user icon-lg"></i>
          </span>
        </div>
        <div>
          <p class="mb-0 text-body-secondary">Total Leads</p>
          <h4 class="mb-0 fw-semibold">{{ number_format($totalLeads) }}</h4>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3 mb-6 mb-xl-0">
    <div class="card h-100">
      <div class="card-body d-flex align-items-center gap-4">
        <div class="avatar avatar-lg flex-shrink-0">
          <span class="avatar-initial rounded bg-label-success">
            <i class="icon-base bx bx-plus-circle icon-lg"></i>
          </span>
        </div>
        <div>
          <p class="mb-0 text-body-secondary">New Today</p>
          <h4 class="mb-0 fw-semibold">{{ number_format($newToday) }}</h4>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3 mb-6 mb-xl-0">
    <div class="card h-100">
      <div class="card-body d-flex align-items-center gap-4">
        <div class="avatar avatar-lg flex-shrink-0">
          <span class="avatar-initial rounded bg-label-info">
            <i class="icon-base bx bx-check-circle icon-lg"></i>
          </span>
        </div>
        <div>
          <p class="mb-0 text-body-secondary">Booked Leads</p>
          <h4 class="mb-0 fw-semibold">{{ number_format($bookedLeads) }}</h4>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3 mb-6 mb-xl-0">
    <div class="card h-100">
      <div class="card-body d-flex align-items-center gap-4">
        <div class="avatar avatar-lg flex-shrink-0">
          <span class="avatar-initial rounded bg-label-warning">
            <i class="icon-base bx bx-trending-up icon-lg"></i>
          </span>
        </div>
        <div>
          <p class="mb-0 text-body-secondary">Conversion Rate</p>
          <h4 class="mb-0 fw-semibold">{{ $conversionRate }}%</h4>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Row 2: Leads by Month + Leads by Status --}}
<div class="row mb-6">
  <div class="col-xxl-8 mb-6 mb-xxl-0">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title m-0">Leads by Month ({{ now()->year }})</h5>
      </div>
      <div class="card-body">
        <div id="leadsMonthlyChart"></div>
      </div>
    </div>
  </div>

  <div class="col-xxl-4">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title m-0">Leads by Status</h5>
      </div>
      <div class="card-body d-flex flex-column align-items-center justify-content-center">
        <div id="leadsByStatusChart"></div>
      </div>
    </div>
  </div>
</div>

{{-- Row 3: Leads by Source + Leads by Segment --}}
<div class="row mb-6">
  <div class="col-md-6 mb-6 mb-md-0">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title m-0">Leads by Source</h5>
      </div>
      <div class="card-body">
        <div id="leadsBySourceChart"></div>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title m-0">Leads by Segment</h5>
      </div>
      <div class="card-body d-flex flex-column align-items-center justify-content-center">
        <div id="leadsBySegmentChart"></div>
      </div>
    </div>
  </div>
</div>

{{-- Row 4: Top Vehicle Models --}}
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title m-0">Top Vehicle Models by Enquiry</h5>
      </div>
      <div class="card-body">
        <div id="topVehicleModelsChart"></div>
      </div>
    </div>
  </div>
</div>

@if($showTodayFollowUps ?? true)
{{-- Row 5: Today's Follow-ups --}}
<div class="row mt-6">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Today's Follow-ups</h5>
        @can('reports:follow-up')
        <a href="{{ route('reports.follow-up', ['period' => 'today']) }}" class="btn btn-sm btn-outline-primary">
          View All
        </a>
        @endcan
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table mb-0">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Lead Name</th>
                <th>Mobile</th>
                <th>Company</th>
                <th>Location</th>
                <th>Status</th>
                <th>Follow-up Date</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($todayFollowUps as $lead)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $lead->first_name }} {{ $lead->last_name }}</td>
                  <td>{{ $lead->mobile_no }}</td>
                  <td>{{ $lead->company->name ?? '-' }}</td>
                  <td>{{ $lead->location->name ?? '-' }}</td>
                  <td>{{ $lead->leadStatus->name ?? '-' }}</td>
                  <td>{{ $lead->next_follow_up ? $lead->next_follow_up->format('d M Y') : '-' }}</td>
                  <td>
                    <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-sm btn-outline-primary">
                      <i class="bx bx-show"></i>
                    </a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center text-muted py-4">No follow-ups scheduled for today.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endif

@endsection

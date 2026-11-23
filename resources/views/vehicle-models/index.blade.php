@extends('layouts/contentNavbarLayout')

@section('title', 'Vehicle Models')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
@endsection

@section('page-style')
    <style>
        .avatar-initial { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; color: white; }
        .fs-12px { font-size: 0.75rem !important; }
        .model-card { min-height: 100px; }
    </style>
@endsection

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-0">Vehicle Models</h4>
      </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Advanced Filters --}}
    <div class="card mb-4">
        <div class="card-body p-3">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small">Model Name</label>
                    <input type="text" class="form-control" name="name" value="{{ request('name') }}" placeholder="SUV">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Segment</label>
                    <select class="form-select" name="segment_id">
                        <option value="">All Segments</option>
                        @foreach($segments as $segment)
                            <option value="{{ $segment->id }}" {{ request('segment_id') == $segment->id ? 'selected' : '' }}>
                                {{ $segment->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Fuel Type</label>
                    <select class="form-select" name="fuel_type_id">
                        <option value="">All Fuels</option>
                        @foreach($fuelTypes as $fuelType)
                            <option value="{{ $fuelType->id }}" {{ request('fuel_type_id') == $fuelType->id ? 'selected' : '' }}>
                                {{ $fuelType->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Company</label>
                    <select class="form-select" name="company_id">
                        <option value="">All Companies</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bx bx-filter-alt"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Stats & CTA --}}
    <div class="row mb-4 g-3">
        <div class="col-md-3">
            <div class="card model-card">
                <div class="card-body text-center">
                    <i class="bx bx-car text-primary bx-lg mb-2"></i>
                    <h3 class="text-primary mb-1">{{ $activeModels }}</h3>
                    <small class="text-muted fw-semibold">Active Models</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card model-card">
                <div class="card-body text-center">
                    <i class="bx bx-pause-circle text-warning bx-lg mb-2"></i>
                    <h3 class="text-warning mb-1">{{ $inactiveModels }}</h3>
                    <small class="text-muted fw-semibold">Inactive</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card model-card">
                <div class="card-body text-center">
                    <i class="bx bx-buildings bx-lg mb-2 text-info"></i>
                    <h3 class="mb-1">{{ $companies->count() }}</h3>
                    <small class="text-muted fw-semibold">Companies</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card model-card">
                <div class="card-body text-center">
                    <a href="{{ route('vehicle-models.create') }}" class="btn btn-primary w-100 h-100 d-flex align-items-center justify-content-center">
                        <i class="bx bx-plus fs-5 me-2"></i>
                        <div>Add Model</div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Models Table --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Vehicle Models ({{ $models->total() }} found)</h5>
                <small class="text-muted">Manage vehicle models and their relations</small>
            </div>
            <div class="d-flex gap-2">
                @can('vehicle-models:create')
                    <a href="{{ route('vehicle-models.import.template') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bx bx-download me-1"></i> Sample CSV
                    </a>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#vehicleModelImportForm" aria-expanded="false" aria-controls="vehicleModelImportForm">
                        <i class="bx bx-upload me-1"></i> Bulk Import
                    </button>
                @endcan
                <a href="{{ route('vehicle-models.create') }}" class="btn btn-primary btn-sm">
                    <i class="bx bx-plus me-1"></i> Add Model
                </a>
            </div>
        </div>
        @can('vehicle-models:create')
            <div class="collapse border-top" id="vehicleModelImportForm">
                <div class="card-body">
                    <form method="POST" action="{{ route('vehicle-models.import') }}" enctype="multipart/form-data" class="row g-3 align-items-end">
                        @csrf
                        <div class="col-md-6 col-lg-4">
                            <label class="form-label" for="vehicleModelImportFile">Select CSV file</label>
                            <input id="vehicleModelImportFile" type="file" name="file" accept=".csv" class="form-control" required>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <button type="submit" class="btn btn-primary mt-3 mt-md-0">
                                <i class="bx bx-upload me-1"></i> Upload & Import
                            </button>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">
                                Only CSV files generated from the Sample CSV template are supported. Use segment_code, company_codes, dealer_location_codes and names for transmissions, fuel types, colours.
                            </small>
                        </div>
                    </form>
                </div>
            </div>
        @endcan
        <div class="table-responsive">
            <table class="table card-table table-striped table-hover" id="modelsTable">
                <thead class="table-light">
                    <tr>
                        <th>Model</th>
                        <th>Segment</th>
                        <th>Fuel</th>
                        <th>Transmission</th>
                        <th>Company</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($models as $model)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        <div class="avatar-initial bg-primary rounded-circle">
                                            {{ strtoupper(substr($model->name, 0, 2)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $model->name }}</div>
                                        <small class="text-muted">ID: {{ $model->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-label-info fs-12px">{{ $model->segment->name }}</span>
                            </td>
                            <td>
                                @foreach($model->fuelTypes as $fuelType)
                                    <span class="badge bg-label-success fs-12px me-1 mb-1">{{ $fuelType->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                @foreach($model->transmissions as $transmission)
                                    <span class="badge bg-label-warning fs-12px me-1 mb-1">{{ $transmission->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                @foreach($model->companies as $company)
                                    <div class="fw-semibold fs-12px">{{ $company->name }}</div>
                                    <small class="text-muted d-block mb-1">{{ $company->code }}</small>
                                @endforeach
                            </td>
                            <td>
                                @foreach($model->dealerLocations as $location)
                                    <small class="text-muted d-block">{{ $location->city }}, {{ $location->name }}</small>
                                @endforeach
                            </td>
                            <td>
                                @if($model->status === 'active')
                                    <span class="badge bg-label-success">Active</span>
                                @else
                                    <span class="badge bg-label-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn p-0" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('vehicle-models.show', $model) }}"><i class="bx bx-show me-2"></i>View</a></li>
                                        <li><a class="dropdown-item" href="{{ route('vehicle-models.edit', $model) }}"><i class="bx bx-edit me-2"></i>Edit</a></li>
                                        <li>
                                            <form method="POST"
                                                action="{{ route('vehicle-models.destroy', $model) }}"
                                                class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bx bx-trash me-2"></i>Delete
                                                </button>
                                            </form>

                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bx bx-car bx-lg text-muted mb-3"></i>
                                <h5 class="text-muted mb-3">No vehicle models found</h5>
                                <p class="text-muted mb-4">Configure your first vehicle model</p>
                                <a href="{{ route('vehicle-models.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus me-2"></i>Create Model
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $models->appends(request()->query())->links() }}
        </div>
    </div>
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
@endsection

@section('page-script')
<script>
    
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

});


$(function() {
    $('#modelsTable').DataTable({
        pageLength: 10,
        responsive: true,
        order: [[0, 'asc']],
        columnDefs: [{ orderable: false, targets: -1 }]
    });
});
</script>
@endsection

@extends('layouts/contentNavbarLayout')

@section('title', 'Dealer Locations')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
@endsection

@section('page-style')
    <style>
        .avatar-initial {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            color: white;
        }
        .fs-12px { font-size: 0.75rem !important; }
        .form-container {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.5rem;
            border: 1px solid #e3e6f0;
            margin-bottom: 1.5rem;
        }
    </style>
@endsection

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-0">Dealer Locations</h4>
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

    {{-- Search & Filter Container --}}
    <div class="card mb-4">
        <div class="card-header pb-0">
            <h5 class="mb-0">Quick Filters</h5>
        </div>
        <div class="card-body form-container">
            <form method="GET" action="{{ route('dealer-locations.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Location Name</label>
                    <input type="text" class="form-control" name="name" value="{{ request('name') }}" placeholder="Enter location name">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Code</label>
                    <input type="text" class="form-control" name="code" value="{{ request('code') }}" placeholder="DL001">
                </div>
                <div class="col-md-2">
                    <label class="form-label">City</label>
                    <input type="text" class="form-control" name="city" value="{{ request('city') }}" placeholder="Chennai">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Company</label>
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
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">All</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bx bx-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- DataTable --}}
    <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Dealer Locations List</h5>
                <small class="text-muted">{{ $dealerLocations->total() }} total locations</small>
            </div>
            <div class="d-flex gap-2">
                @can('dealer-locations:create')
                    <a href="{{ route('dealer-locations.import.template') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bx bx-download me-1"></i> Sample CSV
                    </a>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#dealerLocationImportForm" aria-expanded="false" aria-controls="dealerLocationImportForm">
                        <i class="bx bx-upload me-1"></i> Bulk Import
                    </button>
                @endcan
                <button class="btn btn-outline-primary btn-sm" id="exportBtn">
                    <i class="bx bx-export me-1"></i> Export
                </button>
                <a href="{{ route('dealer-locations.create') }}" class="btn btn-primary btn-sm">
                    <i class="bx bx-plus me-1"></i> Add Location
                </a>
            </div>
        </div>
        @can('dealer-locations:create')
            <div class="collapse border-top" id="dealerLocationImportForm">
                <div class="card-body">
                    <form method="POST" action="{{ route('dealer-locations.import') }}" enctype="multipart/form-data" class="row g-3 align-items-end">
                        @csrf
                        <div class="col-md-6 col-lg-4">
                            <label class="form-label" for="dealerLocationImportFile">Select CSV file</label>
                            <input id="dealerLocationImportFile" type="file" name="file" accept=".csv" class="form-control" required>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <button type="submit" class="btn btn-primary mt-3 mt-md-0">
                                <i class="bx bx-upload me-1"></i> Upload & Import
                            </button>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">
                                Use the Sample CSV template. <strong>company_code</strong> must match an existing Company code (e.g. MAH).
                            </small>
                        </div>
                    </form>
                </div>
            </div>
        @endcan
        <div class="table-responsive">
            <table class="table card-table table-striped table-hover" id="dealerLocationsTable">
                <thead class="table-light">
                    <tr>
                        <th>Location</th>
                        <th>Code</th>
                        <th>Company</th>
                        <th>City</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dealerLocations as $location)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        <div class="avatar-initial bg-success rounded-circle">
                                            {{ strtoupper(substr($location->name, 0, 2)) }}
                                        </div>
                                    </div>
                                    {{ $location->name }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-label-info fs-12px">{{ $location->code }}</span>
                            </td>
                            <td>
                                <span class="badge bg-label-primary fs-12px">{{ $location->company->name ?? '-' }}</span>
                            </td>
                            <td>{{ $location->city ?? '-' }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    @if($location->contact_number)
                                        <small class="text-muted">
                                            <i class="bx bx-phone me-1"></i>{{ $location->contact_number }}
                                        </small>
                                    @endif
                                    @if($location->email)
                                        <small class="text-muted">{{ $location->email }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($location->status === 'active')
                                    <span class="badge bg-label-success">Active</span>
                                @else
                                    <span class="badge bg-label-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn p-0 btn-sm" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="{{ route('dealer-locations.show', $location) }}">
                                            <i class="bx bx-show me-1"></i> View</a></li>
                                        <li><a class="dropdown-item" href="{{ route('dealer-locations.edit', $location) }}">
                                            <i class="bx bx-edit me-1"></i> Edit</a></li>
                                        <li>
                                            <form method="POST" action="{{ route('dealer-locations.destroy', $location) }}" class="d-inline delete-form">
                                                @csrf @method('DELETE')
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
                            <td colspan="7" class="text-center py-5">
                                <i class="bx bx-store-alt fs-1 text-muted mb-3"></i>
                                <p class="mb-0 text-muted">No dealer locations found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            @if($dealerLocations->hasPages())
                <div class="text-muted">Showing {{ $dealerLocations->firstItem() ?? 0 }} to {{ $dealerLocations->lastItem() ?? 0 }} of {{ $dealerLocations->total() }} locations</div>
                <div>{{ $dealerLocations->links() }}</div>
            @endif
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
    const table = $('#dealerLocationsTable').DataTable({
        pageLength: 10,
        responsive: true,
        dom: 'Bfrtip',
        language: { search: "Search locations:", lengthMenu: "_MENU_ per page" },
        order: [[0, 'asc']],
        columnDefs: [{ orderable: false, targets: -1 }],
        buttons: ['excel', 'pdf']
    });

    $('#exportBtn').on('click', function() {
        table.buttons().trigger();
    });
});
</script>
@endsection

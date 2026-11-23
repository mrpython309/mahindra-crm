@extends('layouts/contentNavbarLayout')

@section('title', 'Variants Management')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
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
    .variant-card { min-height: 100px; }
</style>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Variants Management</h4>
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
                <div class="col-md-3">
                    <label class="form-label small">Search Variants</label>
                    <input type="text" class="form-control" name="name" value="{{ request('name') }}" placeholder="Search by variant name">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Vehicle Model</label>
                    <select class="form-select" name="vehicle_model_id">
                        <option value="">All Models</option>
                        @foreach($vehicleModels as $model)
                            <option value="{{ $model->id }}" {{ request('vehicle_model_id') == $model->id ? 'selected' : '' }}>
                                {{ $model->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Status</label>
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bx bx-filter-alt"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row mb-4 g-3">
        <div class="col-md-3">
            <div class="card variant-card">
                <div class="card-body text-center">
                    <i class="bx bx-car text-primary bx-lg mb-2"></i>
                    <h3 class="text-primary mb-1">{{ $activeVariants }}</h3>
                    <small class="text-muted fw-semibold">Active Variants</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card variant-card">
                <div class="card-body text-center">
                    <i class="bx bx-pause-circle text-warning bx-lg mb-2"></i>
                    <h3 class="text-warning mb-1">{{ $inactiveVariants }}</h3>
                    <small class="text-muted fw-semibold">Inactive Variants</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card variant-card">
                <div class="card-body text-center">
                    <i class="bx bx-collection bx-lg mb-2 text-info"></i>
                    <h3 class="text-info mb-1">{{ $variants->total() }}</h3>
                    <small class="text-muted fw-semibold">Total Variants</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card variant-card">
                <div class="card-body text-center">
                    <a href="{{ route('variants.create') }}" class="btn btn-primary w-100 h-100 d-flex align-items-center justify-content-center">
                        <i class="bx bx-plus fs-5 me-2"></i>
                        <div>Add Variant</div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Variants Table --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Vehicle Variants ({{ $variants->total() }} found)</h5>
                <small class="text-muted">Manage variants and their fuel/transmission/colour links</small>
            </div>
            <div class="d-flex gap-2">
                @can('variants:create')
                    <a href="{{ route('variants.import.template') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bx bx-download me-1"></i> Sample CSV
                    </a>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#variantImportForm" aria-expanded="false" aria-controls="variantImportForm">
                        <i class="bx bx-upload me-1"></i> Bulk Import
                    </button>
                @endcan
                <a href="{{ route('variants.create') }}" class="btn btn-primary btn-sm">
                    <i class="bx bx-plus me-1"></i> Add Variant
                </a>
            </div>
        </div>
        @can('variants:create')
            <div class="collapse border-top" id="variantImportForm">
                <div class="card-body">
                    <form method="POST" action="{{ route('variants.import') }}" enctype="multipart/form-data" class="row g-3 align-items-end">
                        @csrf
                        <div class="col-md-6 col-lg-4">
                            <label class="form-label" for="variantImportFile">Select CSV file</label>
                            <input id="variantImportFile" type="file" name="file" accept=".csv,.txt" class="form-control" required>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <button type="submit" class="btn btn-primary mt-3 mt-md-0">
                                <i class="bx bx-upload me-1"></i> Upload & Import
                            </button>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">Use the Sample CSV template. vehicle_model_name, fuel_type_names and transmission_names must belong to the vehicle model; colour_names optional.</small>
                        </div>
                    </form>
                </div>
            </div>
        @endcan
        <div class="table-responsive">
            <table class="table card-table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Variant</th>
                        <th>Vehicle Model</th>
                        <th>Fuel Types</th>
                        <th>Transmissions</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($variants as $variant)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        <div class="avatar-initial bg-primary rounded-circle">
                                            {{ strtoupper(substr($variant->name, 0, 2)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $variant->name }}</div>
                                        <small class="text-muted">ID: {{ $variant->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $variant->vehicleModel->name }}</div>
                                <small class="text-muted">{{ $variant->vehicleModel->segment->name ?? 'N/A' }}</small>
                            </td>
                            <td>
                                @foreach($variant->fuelTypes as $fuelType)
                                    <span class="badge bg-label-success fs-12px me-1 mb-1">{{ $fuelType->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                @foreach($variant->transmissions as $transmission)
                                    <span class="badge bg-label-info fs-12px me-1 mb-1">{{ $transmission->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                @if($variant->status === 'active')
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
                                        <li><a class="dropdown-item" href="{{ route('variants.edit', $variant) }}">
                                            <i class="bx bx-edit-alt me-1"></i>Edit</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('variants.destroy', $variant) }}" method="POST" 
                                                  onsubmit="return confirm('Are you sure you want to delete this variant?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bx bx-trash me-1"></i>Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bx bx-car bx-lg text-muted mb-3"></i>
                                <div class="fw-semibold text-muted">No variants found</div>
                                <small class="text-muted">Create your first variant to get started</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($variants->hasPages())
        <div class="card-footer">
            {{ $variants->links() }}
        </div>
        @endif
    </div>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    // Auto-submit form on filter change
    $('select[name="vehicle_model_id"], select[name="status"]').on('change', function() {
        $(this).closest('form').submit();
    });
});
</script>
@endsection
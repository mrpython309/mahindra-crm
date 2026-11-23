@extends('layouts/contentNavbarLayout')

@section('title', 'Variants Management')

@section('page-style')
<style>
    .model-card {
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border-radius: 16px;
    }
    .model-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .search-card {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border-radius: 20px;
        border: none;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        padding: 2rem;
        margin-bottom: 2rem;
    }
    .table-responsive {
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    .btn-primary-modern {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border: none;
        border-radius: 12px;
        padding: 0.75rem 2rem;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        transition: all 0.3s ease;
    }
    .btn-primary-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
    }
    .card-table {
        margin-bottom: 0;
    }
    .card-table th {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border: none;
        font-weight: 600;
        color: #334155;
        padding: 1rem;
    }
    .card-table td {
        border: none;
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #e2e8f0;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bx bx-car me-2 text-primary"></i>
                Variants Management
            </h4>
            <small class="text-muted">Manage vehicle model variants and their configurations</small>
        </div>
        <a href="{{ route('variants.create') }}" class="btn btn-primary-modern">
            <i class="bx bx-plus me-2"></i>Create Variant
        </a>
    </div>

    <!-- Search and Filters -->
    <div class="search-card">
        <form method="GET" action="{{ route('variants.index') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-medium">Search Variants</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bx bx-search"></i>
                    </span>
                    <input type="text" 
                           class="form-control border-start-0" 
                           name="name" 
                           value="{{ request('name') }}" 
                           placeholder="Search by variant name">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-medium">Vehicle Model</label>
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
                <label class="form-label fw-medium">Status</label>
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100 me-2">
                    <i class="bx bx-search me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4 g-3">
        <div class="col-md-3">
            <div class="card model-card">
                <div class="card-body text-center">
                    <i class="bx bx-car text-primary bx-lg mb-2"></i>
                    <h3 class="text-primary mb-1">{{ $activeVariants }}</h3>
                    <small class="text-muted fw-semibold">Active Variants</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card model-card">
                <div class="card-body text-center">
                    <i class="bx bx-pause-circle text-warning bx-lg mb-2"></i>
                    <h3 class="text-warning mb-1">{{ $inactiveVariants }}</h3>
                    <small class="text-muted fw-semibold">Inactive Variants</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card model-card">
                <div class="card-body text-center">
                    <i class="bx bx-collection bx-lg mb-2 text-info"></i>
                    <h3 class="text-info mb-1">{{ $variants->total() }}</h3>
                    <small class="text-muted fw-semibold">Total Variants</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card model-card">
                <div class="card-body text-center">
                    <a href="{{ route('variants.create') }}" class="btn btn-primary-modern w-100 h-100 d-flex align-items-center justify-content-center">
                        <i class="bx bx-plus fs-5 me-2"></i>
                        <div>Add Variant</div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Variants Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Vehicle Variants ({{ $variants->total() }} found)</h5>
        </div>
        <div class="table-responsive">
            <table class="table card-table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Variant</th>
                        <th>Vehicle Model</th>
                        <th>Fuel Types</th>
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
                            <td colspan="5" class="text-center py-5">
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
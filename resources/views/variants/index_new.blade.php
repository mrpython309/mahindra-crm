@extends('layouts/contentNavbarLayout')

@section('title', 'Variants Management')

@section('page-style')
<style>
    .variant-card {
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border-radius: 20px;
        background: white;
        position: relative;
        overflow: hidden;
    }
    .variant-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 35px rgba(0,0,0,0.15);
    }
    .variant-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
    }
    .search-card {
        background: linear-gradient(135deg, #fafbff 0%, #f1f5f9 100%);
        border-radius: 24px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 8px 32px rgba(0,0,0,0.08);
        padding: 2.5rem;
        margin-bottom: 2.5rem;
        position: relative;
    }
    .search-card::before {
        content: '';
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6, #06b6d4);
        border-radius: 24px;
        z-index: -1;
        opacity: 0.1;
    }
    .table-container {
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.08);
        overflow: hidden;
        background: white;
        border: 1px solid #e2e8f0;
    }
    .btn-primary-modern {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border: none;
        border-radius: 16px;
        padding: 0.875rem 2.5rem;
        font-weight: 600;
        font-size: 0.925rem;
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.3);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        color: white;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    .btn-primary-modern:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 28px rgba(99, 102, 241, 0.4);
        background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
        color: white;
    }
    .btn-primary-modern:focus {
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3);
        color: white;
    }
    .btn-filter {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        border: none;
        border-radius: 12px;
        color: white;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        transition: all 0.3s ease;
    }
    .btn-filter:hover {
        transform: translateY(-2px);
        background: linear-gradient(135deg, #047857 0%, #065f46 100%);
        box-shadow: 0 8px 20px rgba(5, 150, 105, 0.3);
        color: white;
    }
    .stats-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, var(--icon-bg-1), var(--icon-bg-2));
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .stats-number {
        font-size: 2.25rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.5rem;
    }
    .stats-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .add-variant-card {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        border: none;
        border-radius: 20px;
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
        height: 100%;
        min-height: 140px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
    }
    .add-variant-card:hover {
        transform: translateY(-5px) scale(1.02);
        box-shadow: 0 12px 35px rgba(99, 102, 241, 0.4);
        color: white;
        text-decoration: none;
    }
    .table-modern {
        margin-bottom: 0;
        background: white;
    }
    .table-modern thead th {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border: none;
        font-weight: 700;
        color: #374151;
        padding: 1.25rem 1.5rem;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .table-modern tbody td {
        border: none;
        padding: 1.25rem 1.5rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }
    .table-modern tbody tr:hover {
        background-color: #fafbff;
    }
    .form-control-modern {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-size: 0.925rem;
        transition: all 0.2s ease;
        background: white;
    }
    .form-control-modern:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        background: white;
    }
    .form-select-modern {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-size: 0.925rem;
        background: white;
        transition: all 0.2s ease;
    }
    .form-select-modern:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    .input-group-text-modern {
        background: white;
        border: 2px solid #e2e8f0;
        border-right: none;
        border-radius: 12px 0 0 12px;
        padding: 0.75rem 1rem;
    }
    .empty-state {
        padding: 4rem 2rem;
        text-align: center;
    }
    .empty-state .icon {
        font-size: 4rem;
        color: #cbd5e1;
        margin-bottom: 1.5rem;
    }
    .empty-state h6 {
        color: #475569;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    .empty-state p {
        color: #64748b;
        margin-bottom: 0;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-5 gap-3">
        <div>
            <h2 class="fw-bold mb-2 d-flex align-items-center">
                <div class="me-3" style="width: 48px; height: 48px; background: linear-gradient(135deg, #6366f1, #8b5cf6); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <i class="bx bx-car text-white" style="font-size: 1.5rem;"></i>
                </div>
                Variants Management
            </h2>
            <p class="text-muted mb-0 ms-5 ps-3">Manage vehicle model variants and their configurations</p>
        </div>
        <div class="d-flex gap-3">
            <a href="{{ route('variants.create') }}" class="btn btn-primary-modern">
                <i class="bx bx-plus"></i>
                Create Variant
            </a>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="search-card">
        <div class="d-flex align-items-center mb-4">
            <div class="me-3">
                <i class="bx bx-filter-alt" style="font-size: 1.5rem; color: #6366f1;"></i>
            </div>
            <div>
                <h5 class="mb-1 fw-bold">Filter & Search</h5>
                <p class="text-muted mb-0 small">Find variants using the filters below</p>
            </div>
        </div>
        
        <form method="GET" action="{{ route('variants.index') }}" class="row g-4" id="filterForm">
            <div class="col-lg-4 col-md-6">
                <label class="form-label fw-semibold text-dark mb-2">
                    <i class="bx bx-search me-1"></i>Search Variants
                </label>
                <div class="input-group">
                    <span class="input-group-text input-group-text-modern">
                        <i class="bx bx-search text-muted"></i>
                    </span>
                    <input type="text" 
                           class="form-control form-control-modern border-start-0" 
                           name="name" 
                           value="{{ request('name') }}" 
                           placeholder="Search by variant name..."
                           style="border-left: none;">
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <label class="form-label fw-semibold text-dark mb-2">
                    <i class="bx bx-car me-1"></i>Vehicle Model
                </label>
                <select class="form-select form-select-modern" name="vehicle_model_id" id="vehicleModelFilter">
                    <option value="">All Models</option>
                    @foreach($vehicleModels as $model)
                        <option value="{{ $model->id }}" {{ request('vehicle_model_id') == $model->id ? 'selected' : '' }}>
                            {{ $model->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <label class="form-label fw-semibold text-dark mb-2">
                    <i class="bx bx-toggle-left me-1"></i>Status
                </label>
                <select class="form-select form-select-modern" name="status" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            
            <div class="col-lg-2 col-md-6 d-flex align-items-end">
                <div class="d-flex gap-2 w-100">
                    <button type="submit" class="btn btn-filter flex-grow-1">
                        <i class="bx bx-filter me-1"></i>Filter
                    </button>
                    <a href="{{ route('variants.index') }}" class="btn btn-outline-secondary" title="Clear Filters">
                        <i class="bx bx-x"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-5 g-4">
        <div class="col-xl-3 col-md-6">
            <div class="card variant-card h-100">
                <div class="card-body text-center p-4">
                    <div class="stats-icon mx-auto" style="--icon-bg-1: #10b981; --icon-bg-2: #059669;">
                        <i class="bx bx-check-circle text-white" style="font-size: 1.75rem;"></i>
                    </div>
                    <div class="stats-number text-success">{{ $activeVariants }}</div>
                    <div class="stats-label">Active Variants</div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card variant-card h-100">
                <div class="card-body text-center p-4">
                    <div class="stats-icon mx-auto" style="--icon-bg-1: #f59e0b; --icon-bg-2: #d97706;">
                        <i class="bx bx-pause-circle text-white" style="font-size: 1.75rem;"></i>
                    </div>
                    <div class="stats-number text-warning">{{ $inactiveVariants }}</div>
                    <div class="stats-label">Inactive Variants</div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card variant-card h-100">
                <div class="card-body text-center p-4">
                    <div class="stats-icon mx-auto" style="--icon-bg-1: #3b82f6; --icon-bg-2: #2563eb;">
                        <i class="bx bx-collection text-white" style="font-size: 1.75rem;"></i>
                    </div>
                    <div class="stats-number text-info">{{ $variants->total() }}</div>
                    <div class="stats-label">Total Variants</div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card variant-card h-100">
                <div class="card-body p-0">
                    <a href="{{ route('variants.create') }}" class="add-variant-card w-100 h-100">
                        <i class="bx bx-plus" style="font-size: 2.5rem;"></i>
                        <div class="fw-semibold" style="font-size: 1.1rem;">Add New Variant</div>
                        <small style="opacity: 0.9;">Create a new variant</small>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Variants Table -->
    <div class="table-container">
        <div class="card-header bg-white border-0 p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1 fw-bold d-flex align-items-center">
                        <i class="bx bx-table me-2 text-primary"></i>
                        Vehicle Variants
                    </h5>
                    <p class="text-muted mb-0 small">{{ $variants->total() }} variants found</p>
                </div>
                <div class="d-flex gap-2">
                    @if(request()->hasAny(['name', 'vehicle_model_id', 'status']))
                        <span class="badge bg-primary">Filtered</span>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th style="width: 25%;">Variant</th>
                        <th style="width: 25%;">Vehicle Model</th>
                        <th style="width: 20%;">Fuel Types</th>
                        <th style="width: 15%;">Status</th>
                        <th style="width: 15%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($variants as $variant)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 48px; height: 48px; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; font-weight: 700; font-size: 0.875rem;">
                                            {{ strtoupper(substr($variant->name, 0, 2)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fw-bold mb-1" style="font-size: 1.1rem;">{{ $variant->name }}</div>
                                        <small class="text-muted">ID: {{ $variant->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div class="fw-semibold mb-1">{{ $variant->vehicleModel->name }}</div>
                                    <span class="badge bg-light text-dark">{{ $variant->vehicleModel->segment->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($variant->fuelTypes as $fuelType)
                                        <span class="badge" style="background: linear-gradient(135deg, #10b981, #059669); color: white; font-size: 0.75rem;">{{ $fuelType->name }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                @if($variant->status === 'active')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.8rem; padding: 0.5rem 1rem;">
                                        <i class="bx bx-check-circle me-1"></i>Active
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size: 0.8rem; padding: 0.5rem 1rem;">
                                        <i class="bx bx-pause-circle me-1"></i>Inactive
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-outline-light border-0 rounded-circle p-2" 
                                            data-bs-toggle="dropdown"
                                            style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bx bx-dots-vertical-rounded text-muted"></i>
                                    </button>
                                    <ul class="dropdown-menu shadow-lg border-0" style="border-radius: 12px; min-width: 160px;">
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('variants.edit', $variant) }}">
                                                <i class="bx bx-edit-alt me-2 text-primary"></i>Edit Variant
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider my-2"></li>
                                        <li>
                                            <form action="{{ route('variants.destroy', $variant) }}" method="POST" 
                                                  onsubmit="return confirm('\u26a0\ufe0f Are you sure you want to delete this variant? This action cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger py-2 border-0 bg-transparent w-100 text-start">
                                                    <i class="bx bx-trash me-2"></i>Delete Variant
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="border-0">
                                <div class="empty-state">
                                    <div class="icon">
                                        <i class="bx bx-car"></i>
                                    </div>
                                    <h6>No variants found</h6>
                                    <p>Create your first variant to get started with managing vehicle variants.</p>
                                    <a href="{{ route('variants.create') }}" class="btn btn-primary-modern mt-3">
                                        <i class="bx bx-plus me-2"></i>Create First Variant
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($variants->hasPages())
            <div class="card-footer bg-light border-0 p-4">
                <div class="d-flex justify-content-center">
                    {{ $variants->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    // Auto-submit form on filter change
    $('#vehicleModelFilter, #statusFilter').on('change', function() {
        $('#filterForm').submit();
    });
    
    // Search input with debounce
    let searchTimeout;
    $('input[name="name"]').on('input', function() {
        clearTimeout(searchTimeout);
        const searchValue = $(this).val();
        
        searchTimeout = setTimeout(function() {
            if (searchValue.length >= 2 || searchValue.length === 0) {
                $('#filterForm').submit();
            }
        }, 500);
    });
    
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Add loading state to form submission
    $('#filterForm').on('submit', function() {
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Filtering...');
        
        setTimeout(function() {
            submitBtn.prop('disabled', false).html(originalText);
        }, 3000);
    });
    
    // Animate stats cards on page load
    $('.stats-number').each(function() {
        const $this = $(this);
        const countTo = parseInt($this.text());
        const countDuration = 1000;
        
        $({ countNum: 0 }).animate({
            countNum: countTo
        }, {
            duration: countDuration,
            easing: 'swing',
            step: function() {
                $this.text(Math.floor(this.countNum));
            },
            complete: function() {
                $this.text(countTo);
            }
        });
    });
    
    // Smooth scroll for empty state button
    $('.empty-state a').on('click', function(e) {
        if ($(this).attr('href').includes('#')) {
            e.preventDefault();
            const target = $($(this).attr('href'));
            if (target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 500);
            }
        }
    });
});
</script>
@endsection
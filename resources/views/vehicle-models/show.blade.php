@extends('layouts/contentNavbarLayout')

@section('title', 'Vehicle Model Details')

@section('page-style')
    <style>
        .detail-card {
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #e5e7eb;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        .detail-header {
            background: linear-gradient(135deg, #f7f5ff 0%, #f0f2ff 100%);
            padding: 2rem 2.5rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-section {
            padding: 2rem 2.5rem;
        }
        .section-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .info-item {
            margin-bottom: 1.5rem;
        }
        .info-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }
        .info-value {
            font-size: 1rem;
            font-weight: 500;
            color: #111827;
        }
        .tag-item {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-block;
            margin: 0.25rem;
        }
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-active {
            background: #d1fae5;
            color: #065f46;
        }
        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }
        .btn-modern {
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid px-3 px-md-4">
        {{-- Header --}}
        <div class="detail-card">
            <div class="detail-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #6366f1, #4f46e5); border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                            <i class="bx bx-car text-white" style="font-size: 2rem;"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold">{{ $vehicleModel->name }}</h4>
                            <div class="d-flex align-items-center gap-3">
                                <span class="status-badge {{ $vehicleModel->status === 'active' ? 'status-active' : 'status-inactive' }}">
                                    {{ ucfirst($vehicleModel->status) }}
                                </span>
                                <small class="text-muted">
                                    Created {{ $vehicleModel->created_at->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('vehicle-models.edit', $vehicleModel) }}" class="btn btn-primary btn-modern">
                            <i class="bx bx-edit me-2"></i>Edit Model
                        </a>
                        <a href="{{ route('vehicle-models.index') }}" class="btn btn-outline-secondary btn-modern">
                            <i class="bx bx-arrow-back me-2"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            {{-- Basic Information --}}
            <div class="col-lg-6">
                <div class="detail-card">
                    <div class="detail-section">
                        <div class="section-title">
                            <i class="bx bx-info-circle text-primary"></i>
                            Basic Information
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Model Name</div>
                            <div class="info-value">{{ $vehicleModel->name }}</div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Segment</div>
                            <div class="info-value">
                                @if($vehicleModel->segment)
                                    <span class="tag-item">{{ $vehicleModel->segment->name }}</span>
                                @else
                                    <span class="text-muted">No segment assigned</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Status</div>
                            <div class="info-value">
                                <span class="status-badge {{ $vehicleModel->status === 'active' ? 'status-active' : 'status-inactive' }}">
                                    {{ ucfirst($vehicleModel->status) }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Created</div>
                            <div class="info-value">{{ $vehicleModel->created_at->format('M d, Y \a\t h:i A') }}</div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Last Updated</div>
                            <div class="info-value">{{ $vehicleModel->updated_at->format('M d, Y \a\t h:i A') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Configuration --}}
            <div class="col-lg-6">
                <div class="detail-card">
                    <div class="detail-section">
                        <div class="section-title">
                            <i class="bx bx-cog text-primary"></i>
                            Configuration
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Fuel Types ({{ $vehicleModel->fuelTypes->count() }})</div>
                            <div class="info-value">
                                @if($vehicleModel->fuelTypes->count() > 0)
                                    @foreach($vehicleModel->fuelTypes as $fuelType)
                                        <span class="tag-item">{{ $fuelType->name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">No fuel types configured</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Transmissions ({{ $vehicleModel->transmissions->count() }})</div>
                            <div class="info-value">
                                @if($vehicleModel->transmissions->count() > 0)
                                    @foreach($vehicleModel->transmissions as $transmission)
                                        <span class="tag-item">{{ $transmission->name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">No transmissions configured</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Companies ({{ $vehicleModel->companies->count() }})</div>
                            <div class="info-value">
                                @if($vehicleModel->companies->count() > 0)
                                    @foreach($vehicleModel->companies as $company)
                                        <span class="tag-item">{{ $company->name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">No companies assigned</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Colours ({{ $vehicleModel->colours->count() }})</div>
                            <div class="info-value">
                                @if($vehicleModel->colours->count() > 0)
                                    @foreach($vehicleModel->colours as $colour)
                                        <span class="tag-item">{{ $colour->name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">No colours configured</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Dealer Locations ({{ $vehicleModel->dealerLocations->count() }})</div>
                            <div class="info-value">
                                @if($vehicleModel->dealerLocations->count() > 0)
                                    @foreach($vehicleModel->dealerLocations as $location)
                                        <span class="tag-item">{{ $location->name }} - {{ $location->city }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">No dealer locations assigned</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Variants Section --}}
        @if($vehicleModel->variants && $vehicleModel->variants->count() > 0)
            <div class="detail-card">
                <div class="detail-section">
                    <div class="section-title">
                        <i class="bx bx-list-ul text-primary"></i>
                        Variants ({{ $vehicleModel->variants->count() }})
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Variant Name</th>
                                    <th>Fuel Types</th>
                                    <th>Transmissions</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vehicleModel->variants as $variant)
                                    <tr>
                                        <td>
                                            <strong>{{ $variant->name }}</strong>
                                        </td>
                                        <td>
                                            @if($variant->fuelTypes && $variant->fuelTypes->count() > 0)
                                                @foreach($variant->fuelTypes as $fuelType)
                                                    <small class="badge bg-primary me-1">{{ $fuelType->name }}</small>
                                                @endforeach
                                            @else
                                                <small class="text-muted">No fuel types</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($variant->transmissions && $variant->transmissions->count() > 0)
                                                @foreach($variant->transmissions as $transmission)
                                                    <small class="badge bg-secondary me-1">{{ $transmission->name }}</small>
                                                @endforeach
                                            @else
                                                <small class="text-muted">No transmissions</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $variant->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                                {{ ucfirst($variant->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('variants.show', $variant) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-show"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- Actions --}}
        <div class="d-flex justify-content-end gap-3 mb-4">
            <a href="{{ route('vehicle-models.edit', $vehicleModel) }}" class="btn btn-primary btn-modern px-4">
                <i class="bx bx-edit me-2"></i>Edit Model
            </a>
            <form method="POST" action="{{ route('vehicle-models.destroy', $vehicleModel) }}" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this vehicle model?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-modern px-4">
                    <i class="bx bx-trash me-2"></i>Delete Model
                </button>
            </form>
        </div>
    </div>
@endsection
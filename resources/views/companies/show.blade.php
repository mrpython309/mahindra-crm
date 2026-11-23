@extends('layouts/contentNavbarLayout')

@section('title', 'Company Details')

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
        <div class="detail-card">
            <div class="detail-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #6366f1, #4f46e5); border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                            <i class="bx bx-buildings text-white" style="font-size: 2rem;"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold">{{ $company->name }}</h4>
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge bg-label-primary">{{ $company->code }}</span>
                                <span class="status-badge {{ $company->status === 'active' ? 'status-active' : 'status-inactive' }}">
                                    {{ ucfirst($company->status) }}
                                </span>
                                @if($company->created_at)
                                    <small class="text-muted">
                                        Created {{ $company->created_at->diffForHumans() }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        @can('companies:edit')
                            <a href="{{ route('companies.edit', $company) }}" class="btn btn-primary btn-modern">
                                <i class="bx bx-edit me-2"></i>Edit Company
                            </a>
                        @endcan
                        <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary btn-modern">
                            <i class="bx bx-arrow-back me-2"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="detail-card">
                    <div class="detail-section">
                        <div class="section-title">
                            <i class="bx bx-map text-primary"></i>
                            Address Details
                        </div>

                        <div class="info-item">
                            <div class="info-label">Address Line 1</div>
                            <div class="info-value">{{ $company->address_line_1 ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Address Line 2</div>
                            <div class="info-value">{{ $company->address_line_2 ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">City</div>
                            <div class="info-value">{{ $company->city ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">State</div>
                            <div class="info-value">{{ $company->state ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Country</div>
                            <div class="info-value">{{ $company->country ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Pincode</div>
                            <div class="info-value">{{ $company->pincode ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="detail-card">
                    <div class="detail-section">
                        <div class="section-title">
                            <i class="bx bx-phone-call text-primary"></i>
                            Contact Information
                        </div>

                        <div class="info-item">
                            <div class="info-label">Contact Person</div>
                            <div class="info-value">{{ $company->contact_person ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Contact Number</div>
                            <div class="info-value">{{ $company->contact_number ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value">
                                @if($company->email)
                                    <a href="mailto:{{ $company->email }}">{{ $company->email }}</a>
                                @else
                                    -
                                @endif
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Created At</div>
                            <div class="info-value">
                                {{ $company->created_at ? $company->created_at->format('M d, Y \a\t h:i A') : '-' }}
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Last Updated</div>
                            <div class="info-value">
                                {{ $company->updated_at ? $company->updated_at->format('M d, Y \a\t h:i A') : '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


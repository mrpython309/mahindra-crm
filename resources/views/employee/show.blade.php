@extends('layouts/contentNavbarLayout')

@section('title', 'Employee Details')

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
        #reportPersonsCollapse .list-group-item {
            border-left: none;
            border-right: none;
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
                            <i class="bx bx-user text-white" style="font-size: 2rem;"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold">{{ $employee->employee_name }}</h4>
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <span class="badge bg-label-primary">{{ $employee->emp_code }}</span>
                                <span class="badge bg-label-secondary">{{ $employee->mile_id }}</span>
                                <span class="status-badge {{ $employee->employee_status === 'active' ? 'status-active' : 'status-inactive' }}">
                                    {{ ucfirst($employee->employee_status ?? 'active') }}
                                </span>
                                @if($employee->designation)
                                    <span class="badge bg-label-info">{{ $employee->designation->designation_name }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        @can('employee:edit')
                            <a href="{{ route('employee.edit', $employee) }}" class="btn btn-primary btn-modern">
                                <i class="bx bx-edit me-2"></i>Edit Employee
                            </a>
                        @endcan
                        @can('employee:delete')
                            <form action="{{ route('employee.destroy', $employee) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this employee? This will also remove their login account if they have one.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-modern">
                                    <i class="bx bx-trash me-2"></i>Delete
                                </button>
                            </form>
                        @endcan
                        <a href="{{ route('employee.index') }}" class="btn btn-outline-secondary btn-modern">
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
                            <i class="bx bx-id-card text-primary"></i>
                            Basic Details
                        </div>

                        <div class="info-item">
                            <div class="info-label">Employee Code</div>
                            <div class="info-value">{{ $employee->emp_code ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">MILE ID</div>
                            <div class="info-value">{{ $employee->mile_id ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Date of Joining</div>
                            <div class="info-value">{{ $employee->date_of_joining ? $employee->date_of_joining->format('M d, Y') : '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Designation</div>
                            <div class="info-value">{{ $employee->designation->designation_name ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Reporting Status</div>
                            <div class="info-value">{{ optional($employee->user)->reporting_status ? 'Yes' : 'No' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Report To (up to 3)</div>
                            <div class="info-value">
                                @if(empty($reportingToOrdered))
                                    <span>—</span>
                                @else
                                    <ul class="list-unstyled mb-0">
                                        @foreach($reportingToOrdered as $index => $row)
                                            <li class="mb-1">
                                                Report person {{ $index + 1 }}:
                                                <a href="{{ route('employee.show', $row['id']) }}">{{ $row['name'] }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                                @can('employee:edit')
                                    <a href="{{ route('employee.edit', $employee) }}#reporting-section" class="btn btn-sm btn-outline-primary mt-2">
                                        <i class="bx bx-edit-alt me-1"></i>Edit report-to
                                    </a>
                                @endcan
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Is System User</div>
                            <div class="info-value">{{ ucfirst($employee->is_user ?? 'no') }}</div>
                        </div>
                        @if($employee->is_user === 'yes' && $employee->user && $employee->user->roles->isNotEmpty())
                            <div class="info-item">
                                <div class="info-label">Role</div>
                                <div class="info-value">{{ $employee->user->roles->pluck('name')->join(', ') }}</div>
                            </div>
                        @endif
                        <div class="info-item">
                            <div class="info-label">Left Organization</div>
                            <div class="info-value">{{ ucfirst($employee->is_left ?? 'no') }}</div>
                        </div>
                        @if($employee->is_left === 'yes')
                            <div class="info-item">
                                <div class="info-label">Left Date</div>
                                <div class="info-value">{{ $employee->left_date ? $employee->left_date->format('M d, Y') : '-' }}</div>
                            </div>
                        @endif
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
                            <div class="info-label">Email</div>
                            <div class="info-value">
                                @if($employee->email)
                                    <a href="mailto:{{ $employee->email }}">{{ $employee->email }}</a>
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Mobile Number</div>
                            <div class="info-value">{{ $employee->mobile_number ?? '-' }}</div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Created At</div>
                            <div class="info-value">
                                {{ $employee->created_at ? $employee->created_at->format('M d, Y \a\t h:i A') : '-' }}
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Last Updated</div>
                            <div class="info-value">
                                {{ $employee->updated_at ? $employee->updated_at->format('M d, Y \a\t h:i A') : '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(optional($employee->user)->reporting_status === 0 || $employee->company_id || !empty($departments) || !empty($dealerLocations) || !empty($subsegments) || !empty($reportingToOrdered))
        <div class="row g-4">
            <div class="col-12">
                <div class="detail-card">
                    <div class="detail-section">
                        <div class="section-title">
                            <i class="bx bx-buildings text-primary"></i>
                            Organization Details
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="info-item">
                                    <div class="info-label">Company</div>
                                    <div class="info-value">{{ $employee->company->name ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-item">
                                    <div class="info-label">Segment</div>
                                    <div class="info-value">{{ !empty($segments) ? implode(', ', $segments) : '-' }}</div>
                                </div>
                            </div>
                        </div>

                        @if(!empty($departments))
                            <div class="info-item">
                                <div class="info-label">Departments</div>
                                <div class="info-value">{{ implode(', ', $departments) }}</div>
                            </div>
                        @endif

                        @if(!empty($dealerLocations))
                            <div class="info-item">
                                <div class="info-label">Dealer Locations</div>
                                <div class="info-value">{{ implode(', ', $dealerLocations) }}</div>
                            </div>
                        @endif

                        @if(!empty($subsegments))
                            <div class="info-item">
                                <div class="info-label">Sub Segments</div>
                                <div class="info-value">{{ implode(', ', $subsegments) }}</div>
                            </div>
                        @endif

                        @if(!empty($reportingToNamesOnly))
                            <div class="info-item">
                                <div class="info-label">Reporting To</div>
                                <div class="info-value">{{ implode(', ', $reportingToNamesOnly) }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection

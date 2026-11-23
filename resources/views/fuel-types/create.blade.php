@extends('layouts/contentNavbarLayout')

@section('title', 'Create Fuel Type')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/formvalidation/formValidation.min.css') }}">
@endsection

@section('page-style')
    <style>
        .form-modern {
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #e5e7eb;
            overflow: hidden;
            margin: 0;
            max-width: 100%;
        }
        .input-modern {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 0.875rem 1.25rem;
            transition: all 0.2s ease;
            background: #fafbfc;
            height: 56px;
        }
        .input-modern:focus {
            border-color: #8b5cf6;
            background: white;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
            transform: translateY(-1px);
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
        .status-card {
            background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
            border: 1px solid #e9d5ff;
            border-radius: 12px;
            padding: 1.75rem;
            margin: 1.5rem 0;
        }
        .btn-purple-modern {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            border: none;
            border-radius: 12px;
            padding: 0.875rem 2.5rem;
            font-weight: 600;
            color: white;
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        }
        .form-section { padding: 2.5rem; }
        .examples-grid { background: #f8fafc; border-radius: 12px; padding: 1.5rem; margin-top: 2rem; }
    </style>
@endsection

@section('content')
    <div class="container-fluid px-2 px-md-4 px-lg-5">
        <div class="row g-0">
            <div class="col-12">
                {{-- Full Width Form --}}
                <div class="form-modern mb-4">
                    {{-- Header --}}
                    <div class="p-4 border-bottom" style="background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div style="width: 52px; height: 52px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 14px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bx bx-gas-pump text-white" style="font-size: 1.375rem;"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 fw-semibold">Create Fuel Type</h5>
                                    <small class="text-muted">Add new fuel option for vehicle models</small>
                                </div>
                            </div>
                            <a href="{{ route('fuel-types.index') }}" class="btn btn-outline-secondary px-4 py-2">
                                <i class="bx bx-arrow-back me-1"></i>Back to List
                            </a>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('fuel-types.store') }}" id="fuelTypeForm">
                        @csrf

                        {{-- Fuel Type Name (Full Width) --}}
                        <div class="form-section">
                            <div class="section-title">
                                <i class="bx bx-droplet text-purple-600"></i>
                                Fuel Type Details
                            </div>
                            <div class="row g-0">
                                <div class="col-12">
                                    <label class="form-label fw-medium mb-3">Fuel Type Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control input-modern @error('name') is-invalid @enderror"
                                           id="name" name="name"
                                           value="{{ old('name') }}"
                                           required
                                           placeholder="Petrol, Diesel, CNG, Electric, Hybrid">
                                    @error('name')
                                        <div class="invalid-feedback mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Status Toggle --}}
                        <div class="form-section">
                            <div class="section-title">
                                <i class="bx bx-toggle-right text-purple-600"></i>
                                Status & Availability
                            </div>
                            <div class="status-card">
                                <div class="row align-items-center g-4">
                                    <div class="col-xl-8 col-lg-9">
                                        <div class="form-check form-switch form-switch-lg p-3 rounded-2 bg-white shadow-sm">
                                            <input class="form-check-input" type="checkbox" id="statusSwitch" checked>
                                            <label class="form-check-label fw-semibold fs-5 lh-lg" for="statusSwitch">
                                                Make fuel type active immediately
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-3">
                                        <select class="form-select form-select-lg input-modern @error('status') is-invalid @enderror"
                                                id="status" name="status" required style="height: 64px;">
                                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>
                                                ✅ Active (Live)
                                            </option>
                                            <option value="inactive">⏸️ Inactive (Draft)</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons (Full Width) --}}
                        <div class="p-5 border-top bg-gray-50">
                            <div class="d-flex justify-content-end gap-3">
                                <a href="{{ route('fuel-types.index') }}" class="btn btn-outline-secondary px-6 py-3 fs-6">
                                    <i class="bx bx-x-circle me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-purple-modern px-8 py-3 fs-6">
                                    <i class="bx bx-check-double me-2"></i>Create Fuel Type
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Full Width Examples (Below Form) --}}
                <div class="row g-0">
                    <div class="col-12">
                        <div class="examples-grid">
                            <h6 class="fw-semibold mb-4 pb-2 border-bottom">⚡ Popular Fuel Types</h6>
                            <div class="row g-3 g-md-4">
                                <div class="col-sm-6 col-lg-3">
                                    <div class="text-center p-4 border rounded-3 hover:shadow-lg transition-all cursor-pointer h-100">
                                        <div class="fs-1 text-primary mb-2">⛽</div>
                                        <div class="fw-bold fs-5 mb-1">Petrol</div>
                                        <small class="text-muted">Gasoline (85%)</small>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <div class="text-center p-4 border rounded-3 hover:shadow-lg transition-all cursor-pointer h-100">
                                        <div class="fs-1 text-success mb-2">🛢️</div>
                                        <div class="fw-bold fs-5 mb-1">Diesel</div>
                                        <small class="text-muted">Diesel (12%)</small>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <div class="text-center p-4 border rounded-3 hover:shadow-lg transition-all cursor-pointer h-100">
                                        <div class="fs-1 text-warning mb-2">💨</div>
                                        <div class="fw-bold fs-5 mb-1">CNG</div>
                                        <small class="text-muted">Natural Gas (2%)</small>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <div class="text-center p-4 border rounded-3 hover:shadow-lg transition-all cursor-pointer h-100">
                                        <div class="fs-1 text-info mb-2">⚡</div>
                                        <div class="fw-bold fs-5 mb-1">Electric</div>
                                        <small class="text-muted">EV (1%)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/formvalidation/formValidation.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/formvalidation/bootstrap5.min.js') }}"></script>
@endsection

@section('page-script')
<script>
$(function() {
    // Status sync
    $('#statusSwitch').on('change', function() {
        $('#status').val(this.checked ? 'active' : 'inactive');
    });

    // Form validation
    FormValidation.formValidation('#fuelTypeForm', {
        fields: {
            name: {
                validators: {
                    notEmpty: {
                        message: 'Fuel type name is required'
                    },
                    stringLength: {
                        max: 100,
                        message: 'Maximum 100 characters'
                    }
                }
            }
        },
        plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap5: new FormValidation.plugins.Bootstrap5(),
            submitButton: new FormValidation.plugins.SubmitButton(),
            defaultSubmit: new FormValidation.plugins.DefaultSubmit()
        }
    });
});
</script>
@endsection

@extends('layouts/contentNavbarLayout')

@section('title', 'Create Transmission')

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
            margin: 0 auto;
            max-width: 100%;
        }
        .input-modern {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 1rem 1.5rem;
            transition: all 0.2s ease;
            background: #fafbfc;
            height: 60px;
            font-size: 1rem;
        }
        .input-modern:focus {
            border-color: #10b981;
            background: white;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
            transform: translateY(-2px);
        }
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 3px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .status-card {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 1px solid #bbf7d0;
            border-radius: 16px;
            padding: 2.5rem;
            margin: 2rem 0;
        }
        .btn-success-modern {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            border-radius: 14px;
            padding: 1rem 3rem;
            font-weight: 600;
            font-size: 1.05rem;
            color: white;
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
            min-width: 160px;
        }
        .form-section { padding: 3rem 3.5rem; }
        .examples-grid {
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            border-radius: 16px;
            padding: 2.5rem;
            margin-top: 3rem;
            border: 1px solid #e5e7eb;
        }
    </style>
@endsection

@section('content')
    {{-- Full Width Container --}}
    <div class="container-fluid px-lg-5 px-xl-6">
        <div class="row g-0">
            <div class="col-12">
                <div class="form-modern mb-5">
                    {{-- Full Width Header --}}
                    <div class="p-5 border-bottom" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-4">
                            <div class="d-flex align-items-center gap-4">
                                <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 18px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);">
                                    <i class="bx bx-plus-circle text-white" style="font-size: 1.75rem;"></i>
                                </div>
                                <div>
                                    <h4 class="mb-1 fw-bold">Create Transmission Type</h4>
                                    <p class="text-muted mb-0 fs-6">Add gear transmission option for vehicle models</p>
                                </div>
                            </div>
                            <a href="{{ route('transmissions.index') }}" class="btn btn-outline-secondary px-5 py-2 fs-6">
                                <i class="bx bx-arrow-back me-2"></i>Back to List
                            </a>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('transmissions.store') }}" id="transmissionForm">
                        @csrf

                        {{-- Transmission Name (Full Width) --}}
                        <div class="form-section">
                            <div class="section-title">
                                <i class="bx bx-cog text-green-600" style="font-size: 1.5rem;"></i>
                                Transmission Details
                            </div>
                            <div class="row g-0">
                                <div class="col-12">
                                    <label class="form-label fw-semibold mb-4 fs-5">Transmission Name <span class="text-danger fs-5">*</span></label>
                                    <input type="text"
                                           class="form-control input-modern @error('name') is-invalid @enderror"
                                           id="name" name="name"
                                           value="{{ old('name') }}"
                                           required
                                           placeholder="Manual, Automatic, CVT, AMT, DCT">
                                    @error('name')
                                        <div class="invalid-feedback mt-3 fs-6">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Status Section --}}
                        <div class="form-section">
                            <div class="section-title">
                                <i class="bx bx-toggle-right text-green-600" style="font-size: 1.5rem;"></i>
                                Status & Availability
                            </div>
                            <div class="status-card">
                                <div class="row align-items-center g-4 g-lg-5">
                                    <div class="col-xl-8 col-lg-9">
                                        <div class="form-check form-switch form-switch-xl p-4 rounded-3 shadow-sm bg-white/80 border">
                                            <input class="form-check-input" type="checkbox" id="statusSwitch" checked>
                                            <label class="form-check-label fw-bold fs-5 lh-lg" for="statusSwitch">
                                                Activate transmission immediately
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-3">
                                        <select class="form-select input-modern form-select-xl @error('status') is-invalid @enderror"
                                                id="status" name="status" required style="height: 72px; font-size: 1.1rem;">
                                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>
                                                ✅ Active & Available
                                            </option>
                                            <option value="inactive">⏸️ Inactive</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback mt-2">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="p-5 border-top bg-gray-50">
                            <div class="d-flex justify-content-end gap-4">
                                <a href="{{ route('transmissions.index') }}" class="btn btn-outline-secondary px-8 py-3 fs-6 fw-semibold">
                                    <i class="bx bx-x-circle me-2 fs-5"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-success-modern px-10 py-3 fs-6 fw-bold shadow-lg">
                                    <i class="bx bx-check-double me-3 fs-5"></i>Create Transmission
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Full Width Examples Grid --}}
                <div class="row g-0 mb-5">
                    <div class="col-12">
                        <div class="examples-grid p-0">
                            <div class="p-5">
                                <h5 class="fw-bold mb-4 pb-3 border-bottom border-3 border-success">🔧 Transmission Types Guide</h5>
                                <div class="row g-4">
                                    <div class="col-lg-3 col-md-6">
                                        <div class="text-center p-5 border rounded-3 shadow hover:shadow-xl transition-all h-100 bg-white">
                                            <div class="fs-1 mb-3 text-primary">⚙️</div>
                                            <div class="h5 fw-bold mb-2">Manual</div>
                                            <div class="text-muted mb-3">5/6-Speed MT</div>
                                            <small class="badge bg-primary">Most Popular</small>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="text-center p-5 border rounded-3 shadow hover:shadow-xl transition-all h-100 bg-white">
                                            <div class="fs-1 mb-3 text-success">🔄</div>
                                            <div class="h5 fw-bold mb-2">Automatic</div>
                                            <div class="text-muted mb-3">6-Speed AT</div>
                                            <small class="badge bg-success">Premium</small>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="text-center p-5 border rounded-3 shadow hover:shadow-xl transition-all h-100 bg-white">
                                            <div class="fs-1 mb-3 text-warning">∞</div>
                                            <div class="h5 fw-bold mb-2">CVT</div>
                                            <div class="text-muted mb-3">Continuously Variable</div>
                                            <small class="badge bg-warning">Smooth</small>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="text-center p-5 border rounded-3 shadow hover:shadow-xl transition-all h-100 bg-white">
                                            <div class="fs-1 mb-3 text-info">🤖</div>
                                            <div class="h5 fw-bold mb-2">AMT</div>
                                            <div class="text-muted mb-3">Automated Manual</div>
                                            <small class="badge bg-info">Budget Auto</small>
                                        </div>
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
    $('#statusSwitch').on('change', function() {
        $('#status').val(this.checked ? 'active' : 'inactive');
    });

    FormValidation.formValidation('#transmissionForm', {
        fields: {
            name: {
                validators: {
                    notEmpty: { message: 'Transmission name is required' },
                    stringLength: {
                        min: 2,
                        max: 50,
                        message: 'Name must be 2-50 characters'
                    }
                }
            }
        },
        plugins: {
            bootstrap5: new FormValidation.plugins.Bootstrap5(),
            submitButton: new FormValidation.plugins.SubmitButton()
        }
    });
});
</script>
@endsection

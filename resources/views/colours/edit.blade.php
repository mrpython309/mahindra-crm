@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Colour')

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
                                <i class="bx bx-palette text-white" style="font-size: 1.375rem;"></i>
                            </div>
                            <div>
                                <h5 class="mb-1 fw-semibold">Edit Colour</h5>
                                <small class="text-muted">Update colour option for vehicle models</small>
                            </div>
                        </div>
                        <a href="{{ route('colours.index') }}" class="btn btn-outline-secondary px-4 py-2">
                            <i class="bx bx-arrow-back me-1"></i>Back to List
                        </a>
                    </div>
                </div>

                {{-- FORM --}}
                <form method="POST"
                      action="{{ route('colours.update', $colour) }}"
                      id="colourForm">
                    @csrf
                    @method('PUT')

                    {{-- Colour Name --}}
                    <div class="form-section">
                        <div class="section-title">
                            <i class="bx bx-palette text-purple-600"></i>
                            Colour Details
                        </div>

                        <div class="row g-0">
                            <div class="col-12">
                                <label class="form-label fw-medium mb-3">
                                    Colour Name <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control input-modern @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', $colour->name) }}"
                                       required
                                       placeholder="Red, Blue, White, Black, Silver">
                                @error('name')
                                    <div class="invalid-feedback mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="form-section">
                        <div class="section-title">
                            <i class="bx bx-toggle-right text-purple-600"></i>
                            Status & Availability
                        </div>

                        <div class="status-card">
                            <div class="row align-items-center g-4">
                                <div class="col-xl-8 col-lg-9">
                                    <div class="form-check form-switch form-switch-lg p-3 rounded-2 bg-white shadow-sm">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               id="statusSwitch"
                                               {{ old('status', $colour->status) === 'active' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold fs-5 lh-lg" for="statusSwitch">
                                            Make colour active immediately
                                        </label>
                                    </div>
                                </div>

                                <div class="col-xl-4 col-lg-3">
                                    <select class="form-select form-select-lg input-modern @error('status') is-invalid @enderror"
                                            id="status"
                                            name="status"
                                            required
                                            style="height: 64px;">
                                        <option value="active"
                                            {{ old('status', $colour->status) == 'active' ? 'selected' : '' }}>
                                            ✅ Active (Live)
                                        </option>
                                        <option value="inactive"
                                            {{ old('status', $colour->status) == 'inactive' ? 'selected' : '' }}>
                                            ⏸️ Inactive (Draft)
                                        </option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="p-5 border-top bg-gray-50">
                        <div class="d-flex justify-content-end gap-3">
                            <a href="{{ route('colours.index') }}"
                               class="btn btn-outline-secondary px-6 py-3 fs-6">
                                <i class="bx bx-x-circle me-2"></i>Cancel
                            </a>
                            <button type="submit"
                                    class="btn btn-purple-modern px-8 py-3 fs-6">
                                <i class="bx bx-check-double me-2"></i>Update Colour
                            </button>
                        </div>
                    </div>

                </form>
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
$(function () {

    $('#statusSwitch').on('change', function () {
        $('#status').val(this.checked ? 'active' : 'inactive');
    });

    FormValidation.formValidation('#colourForm', {
        fields: {
            name: {
                validators: {
                    notEmpty: { message: 'Colour name is required' },
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

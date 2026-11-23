@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Transmission')

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
        }
        .input-modern {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 1rem 1.5rem;
            background: #fafbfc;
            height: 60px;
            font-size: 1rem;
        }
        .input-modern:focus {
            border-color: #10b981;
            background: white;
            box-shadow: 0 0 0 4px rgba(16,185,129,.1);
        }
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 3px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .status-card {
            background: linear-gradient(135deg,#f0fdf4,#dcfce7);
            border: 1px solid #bbf7d0;
            border-radius: 16px;
            padding: 2.5rem;
        }
        .btn-success-modern {
            background: linear-gradient(135deg,#10b981,#059669);
            border-radius: 14px;
            padding: 1rem 3rem;
            font-weight: 600;
            color: white;
        }
        .form-section { padding: 3rem 3.5rem; }
    </style>
@endsection

@section('content')
<div class="container-fluid px-lg-5">
    <div class="form-modern mb-5">

        {{-- Header --}}
        <div class="p-5 border-bottom" style="background:#f0fdf4;">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex gap-4 align-items-center">
                    <div style="width:64px;height:64px;background:#10b981;border-radius:18px;display:flex;align-items:center;justify-content:center;">
                        <i class="bx bx-edit text-white fs-3"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 fw-bold">Edit Transmission</h4>
                        <p class="text-muted mb-0">Update transmission type</p>
                    </div>
                </div>
                <a href="{{ route('transmissions.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-2"></i>Back
                </a>
            </div>
        </div>

        <form method="POST"
              action="{{ route('transmissions.update', $transmission) }}"
              id="transmissionForm">
            @csrf
            @method('PUT')

            {{-- Name --}}
            <div class="form-section">
                <div class="section-title">
                    <i class="bx bx-cog"></i> Transmission Details
                </div>

                <label class="fw-semibold fs-5 mb-3">
                    Transmission Name <span class="text-danger">*</span>
                </label>

                <input type="text"
                       name="name"
                       class="form-control input-modern @error('name') is-invalid @enderror"
                       value="{{ old('name', $transmission->name) }}"
                       required>

                @error('name')
                    <div class="invalid-feedback mt-2">{{ $message }}</div>
                @enderror
            </div>

            {{-- Status --}}
            <div class="form-section">
                <div class="section-title">
                    <i class="bx bx-toggle-right"></i> Status
                </div>

                <div class="status-card">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="statusSwitch"
                                       {{ old('status', $transmission->status) === 'active' ? 'checked' : '' }}>
                                <label class="fw-bold fs-5">Active Transmission</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select name="status" id="status" class="form-select input-modern">
                                <option value="active" {{ old('status',$transmission->status)=='active'?'selected':'' }}>Active</option>
                                <option value="inactive" {{ old('status',$transmission->status)=='inactive'?'selected':'' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="p-5 border-top bg-gray-50">
                <div class="d-flex justify-content-end gap-4">
                    <a href="{{ route('transmissions.index') }}" class="btn btn-outline-secondary px-8">
                        Cancel
                    </a>
                    <button class="btn btn-success-modern px-10">
                        <i class="bx bx-save me-2"></i>Update Transmission
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('page-script')
<script>
$(function () {
    $('#statusSwitch').on('change', function () {
        $('#status').val(this.checked ? 'active' : 'inactive');
    });
});
</script>
@endsection

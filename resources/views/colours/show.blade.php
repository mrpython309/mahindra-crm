@extends('layouts/contentNavbarLayout')

@section('title', 'View Colour')

@section('content')
<div class="container-fluid px-2 px-md-4 px-lg-5">
    <div class="row g-0">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Colour Details</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('colours.edit', $colour) }}" class="btn btn-primary btn-sm">
                            <i class="bx bx-edit me-1"></i>Edit
                        </a>
                        <a href="{{ route('colours.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bx bx-arrow-back me-1"></i>Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Colour Name</label>
                                <p class="form-control-static">{{ $colour->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Status</label>
                                <p class="form-control-static">
                                    @if($colour->status === 'active')
                                        <span class="badge bg-label-success">Active</span>
                                    @else
                                        <span class="badge bg-label-secondary">Inactive</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Created At</label>
                                <p class="form-control-static">{{ $colour->created_at->format('d M Y, h:i A') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Updated At</label>
                                <p class="form-control-static">{{ $colour->updated_at->format('d M Y, h:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

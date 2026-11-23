@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Lead Type')

@section('page-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/formvalidation/formValidation.min.css') }}">
@endsection

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    Edit Lead Type
</h4>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">Edit Lead Type</h5>
                <a href="{{ route('enquiry-types.index') }}" class="btn btn-secondary">
                    Back to list
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('enquiry-types.update', $enquiryType->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label" for="name">Lead Type Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $enquiryType->name) }}" 
                               placeholder="Enter lead type name" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="pt-3">
                        <button type="submit" class="btn btn-primary">
                            Update Lead Type
                        </button>
                        <a href="{{ route('enquiry-types.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>

                @if(session('success'))
                    <div class="alert alert-success mt-3">
                        {{ session('success') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<!-- Optional client-side validation can go here -->
@endsection

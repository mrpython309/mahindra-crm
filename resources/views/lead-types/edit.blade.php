@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Lead Type')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    Edit Lead Type
</h4>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">Edit Lead Type</h5>
                <a href="{{ route('lead-types.index') }}" class="btn btn-secondary">
                    Back to list
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('lead-types.update', $leadType->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label" for="name">Lead Type Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $leadType->name) }}" 
                               placeholder="Enter lead type name" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="likely_purchase">Likely Purchase</label>
                        <select class="form-select @error('likely_purchase') is-invalid @enderror" 
                                id="likely_purchase" name="likely_purchase">
                            <option value="">Select Likely Purchase</option>
                            <option value="under_15_days" {{ old('likely_purchase', $leadType->likely_purchase) == 'under_15_days' ? 'selected' : '' }}>Under 15 Days</option>
                            <option value="15_to_45_days" {{ old('likely_purchase', $leadType->likely_purchase) == '15_to_45_days' ? 'selected' : '' }}>15 to 45 Days</option>
                            <option value="above_45_days" {{ old('likely_purchase', $leadType->likely_purchase) == 'above_45_days' ? 'selected' : '' }}>Above 45 Days</option>
                            <option value="invalid_enquiry_closed" {{ old('likely_purchase', $leadType->likely_purchase) == 'invalid_enquiry_closed' ? 'selected' : '' }}>Invalid Enquiry (Closed)</option>
                        </select>
                        @error('likely_purchase')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="pt-3">
                        <button type="submit" class="btn btn-primary">
                            Update Lead Type
                        </button>
                        <a href="{{ route('lead-types.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success mt-3">
                            {{ session('success') }}
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

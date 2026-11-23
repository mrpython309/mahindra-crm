@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Lead Source')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    Edit Lead Source
</h4>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">Edit Lead Source</h5>
                <a href="{{ route('lead-sources.index') }}" class="btn btn-secondary">
                    Back to list
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('lead-sources.update', $leadSource->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label" for="campaign">Campaign <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('campaign') is-invalid @enderror" 
                               id="campaign" name="campaign" value="{{ old('campaign', $leadSource->campaign) }}" 
                               placeholder="Enter campaign name" required>
                        @error('campaign')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="enquiry_type_id">Enquiry Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('enquiry_type_id') is-invalid @enderror" 
                                id="enquiry_type_id" name="enquiry_type_id" required>
                            <option value="">Select Enquiry Type</option>
                            @foreach($enquiryTypes as $type)
                                <option value="{{ $type->id }}" 
                                    {{ old('enquiry_type_id', $leadSource->enquiry_type_id) == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('enquiry_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="pt-3">
                        <button type="submit" class="btn btn-primary">
                            Update Lead Source
                        </button>
                        <a href="{{ route('lead-sources.index') }}" class="btn btn-secondary">
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

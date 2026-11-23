@extends('layouts/contentNavbarLayout')

@section('title', 'Create Lead Source')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    Create Lead Source
</h4>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">New Lead Source</h5>
                <a href="{{ route('lead-sources.index') }}" class="btn btn-secondary">
                    Back to list
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('lead-sources.store') }}">
                    @csrf
                    {{-- Campaign Name --}}
                    <div class="mb-3">
                        <label class="form-label" for="campaign">Campaign <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('campaign') is-invalid @enderror" 
                               id="campaign" name="campaign" value="{{ old('campaign') }}"
                               placeholder="Enter campaign name" required>
                        @error('campaign')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Enquiry Type --}}
                    <div class="mb-3">
                        <label class="form-label" for="enquiry_type_id">Enquiry Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('enquiry_type_id') is-invalid @enderror" 
                                id="enquiry_type_id" name="enquiry_type_id" required>
                            <option value="">Select Enquiry Type</option>
                            @foreach($enquiryTypes as $type)
                                <option value="{{ $type->id }}" {{ old('enquiry_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('enquiry_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Actions --}}
                    <div class="pt-3">
                        <button type="submit" class="btn btn-primary">
                            Create Lead Source
                        </button>
                        <a href="{{ route('lead-sources.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>

                {{-- Success Message --}}
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
{{-- Optional client-side validation or SweetAlert can be added here --}}
@endsection

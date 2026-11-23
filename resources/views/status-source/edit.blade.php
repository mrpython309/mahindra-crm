@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Lead Response')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    Edit Lead Response
</h4>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">Edit Lead Response</h5>
                <a href="{{ route('status-sources.index') }}" class="btn btn-secondary">
                    Back to list
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('status-sources.update', $statusSource->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Response <span class="text-danger">*</span></label>
                        <input type="text" name="response" class="form-control @error('response') is-invalid @enderror" 
                               value="{{ old('response', $statusSource->response) }}" placeholder="Enter response" required>
                        @error('response')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lead Stage <span class="text-danger">*</span></label>
                        <select class="form-select @error('lead_status_id') is-invalid @enderror"
                                name="lead_status_id" required>
                            <option value="">Select Lead Stage</option>
                            @foreach($leadStatuses as $status)
                                <option value="{{ $status->id }}"
                                    {{ $statusSource->lead_status_id == $status->id ? 'selected' : '' }}>
                                    {{ ucfirst($status->name) }}
                                </option>
                            @endforeach
                        </select>
                        @error('lead_status_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="pt-3">
                        <button type="submit" class="btn btn-primary">
                            Update Lead Response
                        </button>
                        <a href="{{ route('status-sources.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

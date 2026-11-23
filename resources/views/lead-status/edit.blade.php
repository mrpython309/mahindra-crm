@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Lead Stage')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    Edit Lead Stage
</h4>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">Edit Lead Stage</h5>
                <a href="{{ route('lead-statuses.index') }}" class="btn btn-secondary">
                    Back to list
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('lead-statuses.update', $leadStatus->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label" for="status_name">Status Name <span class="text-danger">*</span></label>
                        <select class="form-select @error('name') is-invalid @enderror" id="status_name" name="name" required>
                            <option value="">Select Status</option>
                            <option value="active" {{ $leadStatus->name === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="closed" {{ $leadStatus->name === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="pt-3">
                        <button type="submit" class="btn btn-primary">
                            Update Lead Stage
                        </button>
                        <a href="{{ route('lead-statuses.index') }}" class="btn btn-secondary">
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

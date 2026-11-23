@extends('layouts/contentNavbarLayout')

@section('title', 'Create Lead Stage')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    Create Lead Stage
</h4>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">New Lead Stage</h5>
                <a href="{{ route('lead-statuses.index') }}" class="btn btn-secondary">
                    Back to list
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('lead-statuses.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label" for="name">Status Name <span class="text-danger">*</span></label>
                        <select class="form-select @error('name') is-invalid @enderror" id="name" name="name" required>
                            <option value="">Select Status</option>
                            <option value="active" {{ old('name') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="closed" {{ old('name') == 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="pt-3">
                        <button type="submit" class="btn btn-primary">
                            Create Lead Stage
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

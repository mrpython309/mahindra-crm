@extends('layouts/contentNavbarLayout')

@section('title', 'Create Brand Model')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    Create Brand Model
</h4>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">New Brand Model</h5>
                <a href="{{ route('brand-models.index') }}" class="btn btn-secondary">
                    Back to list
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('brand-models.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="make_id">Make <span class="text-danger">*</span></label>
                        <select class="form-select @error('make_id') is-invalid @enderror" 
                                id="make_id" name="make_id" required>
                            <option value="">Select Make</option>
                            @foreach($makes as $make)
                                <option value="{{ $make->id }}" {{ old('make_id') == $make->id ? 'selected' : '' }}>
                                    {{ $make->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('make_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="name">Model Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" 
                               placeholder="Enter model name (e.g., Desire, Swift)" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" 
                                id="status" name="status" required>
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="pt-3">
                        <button type="submit" class="btn btn-primary">
                            Create Brand Model
                        </button>
                        <a href="{{ route('brand-models.index') }}" class="btn btn-secondary">
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

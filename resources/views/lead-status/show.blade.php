@extends('layouts/contentNavbarLayout')

@section('title', 'View Lead Stage')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    View Lead Stage
</h4>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">Lead Stage Details</h5>
                <div>
                    <a href="{{ url('/lead-status/1/edit') }}" class="btn btn-warning">Edit</a>
                    <a href="{{ url('/lead-status') }}" class="btn btn-secondary">Back to list</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <strong>ID:</strong> 1
                        </div>
                        <div class="mb-3">
                            <strong>Status Name:</strong> <span class="badge bg-success">Active</span>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
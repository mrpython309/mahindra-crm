@extends('layouts/contentNavbarLayout')

@section('title', 'View Lead Type')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    View Lead Type
</h4>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">Lead Type Details</h5>
                <div>
                    <a href="{{ url('/lead-type-simple/1/edit') }}" class="btn btn-warning">Edit</a>
                    <a href="{{ url('/lead-type-simple') }}" class="btn btn-secondary">Back to list</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <strong>ID:</strong> 1
                        </div>
                        <div class="mb-3">
                            <strong>Lead Type Name:</strong> Hot
                        </div>
                        <div class="mb-3">
                            <strong>Created:</strong> 2 days ago
                        </div>
                        <div class="mb-3">
                            <strong>Last Updated:</strong> 1 day ago
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('layouts/contentNavbarLayout')

@section('title', 'View Lead Response')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    View Lead Response
</h4>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">Lead Response Details</h5>
                <div>
                    <a href="{{ route('status-sources.edit', $statusSource->id) }}" class="btn btn-warning">Edit</a>
                    <a href="{{ route('status-sources.index') }}" class="btn btn-secondary">Back to list</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <strong>ID:</strong> {{ $statusSource->id }}
                        </div>
                        <div class="mb-3">
                            <strong>Response:</strong> {{ $statusSource->response ?? '-' }}
                        </div>
                        <div class="mb-3">
                            <strong>Lead Stage:</strong>
                            @if($statusSource->leadStatus)
                                @if($statusSource->leadStatus->name === 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Closed</span>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

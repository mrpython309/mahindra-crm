@extends('layouts/contentNavbarLayout')

@section('title', 'View Role Permissions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0">Role: {{ $role->name }}</h4>
  <div class="d-flex gap-2">
    @can('roles:edit')
      <a href="{{ route('roles.edit', $role) }}" class="btn btn-primary">
        <i class="bx bx-edit-alt me-1"></i> Edit Role
      </a>
    @endcan
    <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
      <i class="bx bx-arrow-back me-1"></i> Back
    </a>
  </div>
</div>

<div class="card shadow-sm border-0 mb-4">
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label text-muted small mb-0">Role name</label>
        <p class="fw-semibold mb-0">{{ $role->name }}</p>
      </div>
      @if($role->description)
        <div class="col-md-6">
          <label class="form-label text-muted small mb-0">Description</label>
          <p class="mb-0">{{ $role->description }}</p>
        </div>
      @endif
      <div class="col-12">
        <label class="form-label text-muted small mb-0">Total permissions</label>
        <p class="mb-0"><span class="badge bg-primary">{{ $role->permissions->count() }}</span> permissions assigned</p>
      </div>
    </div>
  </div>
</div>

<div class="card shadow-sm border-0">
  <div class="card-header">
    <h5 class="mb-0">Permissions for this role</h5>
  </div>
  <div class="card-body">
    @if($permissionsGrouped->isEmpty())
      <p class="text-muted mb-0">No permissions assigned to this role.</p>
    @else
      @php $sorted = $permissionsGrouped->sortKeys(); @endphp
      @foreach($sorted as $module => $perms)
        <div class="mb-4">
          <h6 class="text-capitalize text-primary mb-2">{{ str_replace(['_', '-'], ' ', $module) }}</h6>
          <div class="d-flex flex-wrap gap-1">
            @foreach($perms as $p)
              <span class="badge bg-label-primary">{{ $p->name }}</span>
            @endforeach
          </div>
        </div>
      @endforeach
    @endif
  </div>
</div>
@endsection

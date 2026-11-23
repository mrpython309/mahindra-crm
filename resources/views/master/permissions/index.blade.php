@extends('layouts/contentNavbarLayout')

@section('title', 'Permissions')

@section('content')
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Permissions</h4>
    <div>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#permissionModal">Create Permission</button>
    </div>
  </div>
  <div class="row mb-3">
    <div class="col-md-4">
      @include('partials.live-search', ['target' => '.table tbody', 'value' => request('q', '')])
    </div>
  </div>
  <div class="card">
    <div class="table-responsive text-nowrap">
      <table class="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($permissions as $p)
            <tr>
              <td>{{ $p->id }}</td>
              <td>{{ $p->name }}</td>
              <td>
                <form action="{{ route('permissions.destroy', $p->id) }}" method="POST" class="d-inline">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-danger">Delete</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="modal fade" id="permissionModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="{{ route('permissions.store') }}" method="POST">
          @csrf
          <div class="modal-header">
            <h5>Create Permission</h5>
            @include('partials.modal-close-button')
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Name</label>
              <input name="name" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer"><button class="btn btn-primary">Save</button></div>
        </form>
      </div>
    </div>
  </div>

@endsection

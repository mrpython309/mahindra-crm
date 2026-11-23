@extends('layouts/contentNavbarLayout')

@section('title', 'Users')

@section('content')
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Users</h4>
    <div>
      @if($canCreate)
        <a href="{{ route('users.create') }}" class="btn btn-primary">
          <i class="bx bx-plus me-1"></i> Add User
        </a>
      @endif
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
            <th>Email</th>
            <th>Store Access</th>
            <th>Created Date</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($users as $u)
            <tr>
              <td>{{ $u->id }}</td>
              <td>
                <div class="fw-medium">{{ $u->name }}</div>
                @if ($u->id === auth()->id())
                  <small class="text-muted">(You)</small>
                @endif
              </td>
              <td>{{ $u->email }}</td>
              <td>
                @if(empty($u->stores) || $u->stores === null)
                  <span class="badge bg-success">All Stores</span>
                @else
                  <span class="badge bg-primary">{{ count($u->stores) }} Store(s)</span>
                  <div>
                    <small class="text-muted">{{ $u->store_names }}</small>
                  </div>
                @endif
              </td>
              <td>
                <div>{{ $u->created_at->format('M d, Y') }}</div>
                <small class="text-muted">{{ $u->created_at->format('h:i A') }}</small>
              </td>
              <td class="text-center">
                <a href="{{ route('users.show', $u->id) }}" class="btn btn-sm btn-primary me-1" title="View">
                  <i class="bx bx-show"></i> View
                </a>
                @if($canEdit)
                  <a href="{{ route('users.edit', $u->id) }}" class="btn btn-sm btn-info me-1" title="Edit">
                    <i class="bx bx-edit"></i> Edit
                  </a>
                @endif
                @if($canEdit)
                  <a href="{{ route('users.roles.edit', $u->id) }}" class="btn btn-sm btn-warning me-1" title="Roles">
                    <i class="bx bx-user-check"></i> Role
                  </a>
                @endif
                @if($canDelete)
                  @if ($u->id !== auth()->id())
                    <form method="POST" action="{{ route('users.destroy', $u->id) }}" class="d-inline"
                      onsubmit="return confirm('Are you sure you want to delete this user?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                        <i class="bx bx-trash"></i> Delete
                      </button>
                    </form>
                  @else
                    <button class="btn btn-sm btn-outline-secondary" title="Cannot delete yourself" disabled>
                      <i class="bx bx-lock"></i> Lock
                    </button>
                  @endif
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3">{{ $users->links() }}</div>
@endsection

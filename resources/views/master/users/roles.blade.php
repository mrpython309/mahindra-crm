@extends('layouts/contentNavbarLayout')

@section('title', 'User Roles')

@section('content')
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Assign Roles for {{ $user->name }}</h4>
    <div>
      <a href="{{ route('users.index') }}" class="btn btn-secondary">Back to Users</a>
    </div>
  </div>

  <div class="card p-3">
    <form method="POST" action="{{ route('users.roles.update', $user->id) }}">
      @csrf
      <div class="row">
        @foreach ($roles as $role)
          <div class="col-md-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->name }}"
                id="role_{{ $role->id }}" {{ $user->hasRole($role->name) ? 'checked' : '' }}>
              <label class="form-check-label" for="role_{{ $role->id }}">{{ $role->name }}</label>
            </div>
          </div>
        @endforeach
      </div>

      <div class="mt-3">
        <button class="btn btn-primary">Save Roles</button>
      </div>
    </form>
  </div>

@endsection

@extends('layouts/contentNavbarLayout')

@section('title', 'Departments')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
  Departments
</h4>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Department List</h5>
        <div class="d-flex gap-2">
            @can('department:create')
                <a href="{{ route('department.import.template') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bx bx-download me-1"></i> Sample CSV
                </a>
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#departmentImportForm" aria-expanded="false" aria-controls="departmentImportForm">
                    <i class="bx bx-upload me-1"></i> Bulk Import
                </button>
            @endcan
            <a href="{{ route('department.create') }}" class="btn btn-primary btn-sm">
                Add Department
            </a>
        </div>
    </div>
    @can('department:create')
        <div class="collapse border-top" id="departmentImportForm">
            <div class="card-body">
                <form method="POST" action="{{ route('department.import') }}" enctype="multipart/form-data" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-6 col-lg-4">
                        <label class="form-label" for="departmentImportFile">Select CSV file</label>
                        <input id="departmentImportFile" type="file" name="file" accept=".csv,.txt" class="form-control" required>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <button type="submit" class="btn btn-primary mt-3 mt-md-0">
                            <i class="bx bx-upload me-1"></i> Upload & Import
                        </button>
                    </div>
                    <div class="col-12">
                        <small class="text-muted">Only CSV files from the Sample CSV template are supported (columns: department_name, status).</small>
                    </div>
                </form>
            </div>
        </div>
    @endcan
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th width="180">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $department)
                    <tr>
                        <td>{{ $department->id }}</td>
                        <td>{{ $department->department_name }}</td>
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                    type="checkbox"
                                    onclick="updateDepartmentStatus(this)"
                                    data-id="{{ $department->id }}"
                                    {{ $department->status === 'active' ? 'checked' : '' }}
                                    style="width:40px;height:20px;cursor:pointer;">
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('department.edit', $department->id) }}"
                               class="btn btn-sm btn-warning">
                                Edit
                            </a>

                            <form action="{{ route('department.destroy', $department->id) }}"
                                  method="POST"
                                  class="d-inline"
                                  id="delete-form-{{ $department->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                        class="btn btn-sm btn-danger"
                                        onclick="confirmDelete({{ $department->id }})">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">
                            No departments found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('page-script')
<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id).submit();
        }
    });
}

function updateDepartmentStatus(el) {

    let departmentId = el.getAttribute('data-id');
    let status = el.checked ? 'active' : 'inactive';

    $.ajax({
        url: "{{ route('department.status') }}",
        type: "PUT",
        data: {
            _token: "{{ csrf_token() }}",
            id: departmentId,
            status: status
        },
        success: function (response) {
            if (!response.success) {
                el.checked = !el.checked;
            }
        },
        error: function () {
            el.checked = !el.checked;
        }
    });
}
</script>
@endsection

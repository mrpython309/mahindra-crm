@extends('layouts/contentNavbarLayout')

@section('title', 'Designations')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
  Designations
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
        <h5 class="mb-0">Designation List</h5>
        <div class="d-flex gap-2">
            @can('designation:create')
                <a href="{{ route('designation.import.template') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bx bx-download me-1"></i> Sample CSV
                </a>
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#designationImportForm" aria-expanded="false" aria-controls="designationImportForm">
                    <i class="bx bx-upload me-1"></i> Bulk Import
                </button>
            @endcan
            <a href="{{ route('designation.create') }}" class="btn btn-primary btn-sm">
                Add Designation
            </a>
        </div>
    </div>
    @can('designation:create')
        <div class="collapse border-top" id="designationImportForm">
            <div class="card-body">
                <form method="POST" action="{{ route('designation.import') }}" enctype="multipart/form-data" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-6 col-lg-4">
                        <label class="form-label" for="designationImportFile">Select CSV file</label>
                        <input id="designationImportFile" type="file" name="file" accept=".csv,.txt" class="form-control" required>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <button type="submit" class="btn btn-primary mt-3 mt-md-0">
                            <i class="bx bx-upload me-1"></i> Upload & Import
                        </button>
                    </div>
                    <div class="col-12">
                        <small class="text-muted">Only CSV files from the Sample CSV template are supported (columns: designation_name, status).</small>
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
                    <th>Designation</th>
                    <th>Status</th>
                    <th width="180">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($designations as $designation)
                    <tr>
                        <td>{{ $designation->id }}</td>
                        <td>{{ $designation->designation_name }}</td>
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                    type="checkbox"
                                    onclick="updateDesignationStatus(this)"
                                    data-id="{{ $designation->id }}"
                                    {{ $designation->status === 'active' ? 'checked' : '' }}
                                    style="width:40px;height:20px;cursor:pointer;">
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('designation.edit', $designation->id) }}"
                               class="btn btn-sm btn-warning">
                                Edit
                            </a>

                            <form action="{{ route('designation.destroy', $designation->id) }}"
                                  method="POST"
                                  class="d-inline"
                                  id="delete-form-{{ $designation->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                        class="btn btn-sm btn-danger"
                                        onclick="confirmDelete({{ $designation->id }})">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">
                            No designations found.
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

function updateDesignationStatus(el) {

    let designationId = el.getAttribute('data-id');
    let status = el.checked ? 'active' : 'inactive';

    $.ajax({
        url: "{{ route('designation.status') }}",
        type: "PUT",
        data: {
            _token: "{{ csrf_token() }}",
            id: designationId,
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

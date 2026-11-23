@extends('layouts/contentNavbarLayout')

@section('title', 'Brand Models')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
  Brand Models
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
        <h5 class="mb-0">Brand Models List</h5>
        <div class="d-flex gap-2">
            @can('brand-models:create')
                <a href="{{ route('brand-models.import.template') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bx bx-download me-1"></i> Sample CSV
                </a>
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#brandModelImportForm" aria-expanded="false" aria-controls="brandModelImportForm">
                    <i class="bx bx-upload me-1"></i> Bulk Import
                </button>
            @endcan
            <a href="{{ route('brand-models.create') }}" class="btn btn-primary btn-sm">
                Add Brand Model
            </a>
        </div>
    </div>
    @can('brand-models:create')
        <div class="collapse border-top" id="brandModelImportForm">
            <div class="card-body">
                <form method="POST" action="{{ route('brand-models.import') }}" enctype="multipart/form-data" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-6 col-lg-4">
                        <label class="form-label" for="brandModelImportFile">Select CSV file</label>
                        <input id="brandModelImportFile" type="file" name="file" accept=".csv,.txt" class="form-control" required>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <button type="submit" class="btn btn-primary mt-3 mt-md-0">
                            <i class="bx bx-upload me-1"></i> Upload & Import
                        </button>
                    </div>
                    <div class="col-12">
                        <small class="text-muted">Only CSV files from the Sample CSV template are supported. make_name must match an existing Make.</small>
                    </div>
                </form>
            </div>
        </div>
    @endcan
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Make</th>
                    <th>Model Name</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $brandModel)
                <tr>
                    <td>{{ $brandModel->id }}</td>
                    <td>{{ $brandModel->make->name }}</td>
                    <td>{{ $brandModel->name }}</td>
                    <td>
                        <span class="badge bg-{{ $brandModel->status == 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($brandModel->status) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('brand-models.edit', $brandModel->id) }}" class="btn btn-sm btn-warning">
                            <i class="bx bx-edit-alt"></i> Edit
                        </a>
                        <button class="btn btn-sm btn-danger" onclick="deleteBrandModel({{ $brandModel->id }})">
                            <i class="bx bx-trash"></i> Delete
                        </button>

                        {{-- Hidden form for delete --}}
                        <form id="delete-form-{{ $brandModel->id }}" method="POST" action="{{ route('brand-models.destroy', $brandModel->id) }}" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">No Brand Models found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('page-script')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function deleteBrandModel(id) {
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
</script>
@endsection

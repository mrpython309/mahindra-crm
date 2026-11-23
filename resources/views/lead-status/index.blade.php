@extends('layouts/contentNavbarLayout')

@section('title', 'Lead Stage')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
@endsection

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
  Lead Stage
</h4>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Lead Stage List</h5>
        <a href="{{ route('lead-statuses.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Add Lead Stage
        </a>
    </div>
    <div class="table-responsive">
        <table id="leadStatusTable" class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Status Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $status)
                <tr>
                    <td>{{ $status->id }}</td>
                    <td>{{ $status->name }}</td>
                    <td>
                        <a href="{{ route('lead-statuses.edit', $status->id) }}" class="btn btn-sm btn-warning">
                            <i class="bx bx-edit-alt me-1"></i> Edit
                        </a>
                        <form action="{{ route('lead-statuses.destroy', $status->id) }}" method="POST" class="d-inline" id="delete-form-{{ $status->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete({{ $status->id }})">
                                <i class="bx bx-trash me-1"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
@endsection

@section('page-script')
<script>
$(function() {
    $('#leadStatusTable').DataTable({
        pageLength: 10,
        responsive: true,
        dom: 'Bfrtip',
        language: {
            search: 'Search:',
            lengthMenu: '_MENU_ per page',
            info: 'Showing _START_ to _END_ of _TOTAL_ stages',
            emptyTable: 'No lead stages found'
        },
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: -1 }
        ],
        buttons: [
            { extend: 'excel', text: '<i class="bx bx-spreadsheet me-1"></i> Excel', className: 'btn btn-sm btn-outline-success me-1' },
            { extend: 'pdf',   text: '<i class="bx bx-file me-1"></i> PDF',   className: 'btn btn-sm btn-outline-danger' }
        ]
    });
});

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
</script>
@endsection

@extends('layouts/contentNavbarLayout')

@section('title', 'Lead Types')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
@endsection

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
  Lead Types
</h4>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Lead Types List</h5>
        <a href="{{ route('lead-types.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Add Lead Type
        </a>
    </div>

    <div class="table-responsive">
        <table id="leadTypesTable" class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Lead Type Name</th>
                    <th>Likely Purchase</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $leadType)
                <tr>
                    <td>{{ $leadType->id }}</td>
                    <td>{{ $leadType->name }}</td>
                    <td>
                        @if($leadType->likely_purchase)
                            @if($leadType->likely_purchase == 'under_15_days')
                                <span class="badge bg-label-success">Under 15 Days</span>
                            @elseif($leadType->likely_purchase == '15_to_45_days')
                                <span class="badge bg-label-warning">15 to 45 Days</span>
                            @elseif($leadType->likely_purchase == 'above_45_days')
                                <span class="badge bg-label-danger">Above 45 Days</span>
                            @elseif($leadType->likely_purchase == 'invalid_enquiry_closed')
                                <span class="badge bg-label-secondary">Invalid Enquiry (Closed)</span>
                            @else
                                {{ $leadType->likely_purchase }}
                            @endif
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('lead-types.edit', $leadType->id) }}" class="btn btn-sm btn-warning">
                            <i class="bx bx-edit-alt me-1"></i> Edit
                        </a>
                        <form action="{{ route('lead-types.destroy', $leadType->id) }}" method="POST" class="d-inline" id="delete-form-{{ $leadType->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete({{ $leadType->id }})">
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
    $('#leadTypesTable').DataTable({
        pageLength: 10,
        responsive: true,
        dom: 'Bfrtip',
        language: {
            search: 'Search:',
            lengthMenu: '_MENU_ per page',
            info: 'Showing _START_ to _END_ of _TOTAL_ lead types',
            emptyTable: 'No lead types found'
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

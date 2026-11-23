@extends('layouts/contentNavbarLayout')

@section('title', 'Lead Sources')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
@endsection

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
  Lead Sources
</h4>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Lead Sources List</h5>
        <a href="{{ route('lead-sources.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Add Lead Source
        </a>
    </div>

    <div class="table-responsive">
        <table id="leadSourcesTable" class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Campaign</th>
                    <th>Enquiry Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->campaign }}</td>
                    <td>
                        @if($item->enquiryType)
                            <span class="badge
                                @if($item->enquiryType->name === 'Tele-In') bg-primary
                                @elseif($item->enquiryType->name === 'Digital') bg-success
                                @elseif($item->enquiryType->name === 'Walk-In') bg-warning
                                @elseif($item->enquiryType->name === 'Field') bg-danger
                                @else bg-secondary
                                @endif
                            ">
                                {{ $item->enquiryType->name }}
                            </span>
                        @else
                            <span class="badge bg-secondary">N/A</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('lead-sources.edit', $item->id) }}" class="btn btn-sm btn-warning">
                            <i class="bx bx-edit-alt me-1"></i> Edit
                        </a>
                        <form action="{{ route('lead-sources.destroy', $item->id) }}" method="POST" class="d-inline" id="delete-form-{{ $item->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete({{ $item->id }})">
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
    $('#leadSourcesTable').DataTable({
        pageLength: 10,
        responsive: true,
        dom: 'Bfrtip',
        language: {
            search: 'Search:',
            lengthMenu: '_MENU_ per page',
            info: 'Showing _START_ to _END_ of _TOTAL_ sources',
            emptyTable: 'No lead sources found'
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

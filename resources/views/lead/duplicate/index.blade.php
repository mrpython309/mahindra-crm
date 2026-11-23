@extends('layouts/contentNavbarLayout')

@section('title', 'Duplicate Leads')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
@endsection

@section('page-style')
<style>
.table th { white-space: nowrap; }
</style>
@endsection

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    Duplicate Leads
</h4>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Users with Duplicate Leads</h5>
        <span class="badge bg-warning text-dark fs-6">
            <i class='bx bx-copy me-1'></i> Duplicates detected by mobile number + vehicle model
        </span>
    </div>

    <div class="card-body p-0">
        @if($usersWithStats->isEmpty())
            <div class="text-center py-5">
                <i class='bx bx-check-circle text-success' style="font-size: 3rem;"></i>
                <p class="mt-3 text-muted">No duplicate leads found.</p>
            </div>
        @else
        <div class="table-responsive">
            <table id="duplicateLeadsTable" class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>User Name</th>
                        <th>Role</th>
                        <th class="text-center">Duplicate Groups</th>
                        <th class="text-center">Total Duplicate Leads</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usersWithStats as $index => $stat)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <span class="fw-semibold">{{ $stat['user']->name }}</span>
                            <br>
                            <small class="text-muted">{{ $stat['user']->email }}</small>
                        </td>
                        <td>
                            @foreach($stat['user']->roles as $role)
                                <span class="badge bg-label-primary me-1">{{ ucfirst(str_replace('-', ' ', $role->name)) }}</span>
                            @endforeach
                        </td>
                        <td class="text-center">
                            <span class="badge bg-warning text-dark fs-6">{{ $stat['duplicate_groups'] }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-danger fs-6">{{ $stat['total_leads'] }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('leads.duplicates.show', $stat['user_id']) }}"
                               class="btn btn-sm btn-info">
                                <i class='bx bx-show me-1'></i> View Details
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
@endsection

@section('page-script')
<script>
$(function() {
    $('#duplicateLeadsTable').DataTable({
        pageLength: 10,
        responsive: true,
        dom: 'Bfrtip',
        language: {
            search: 'Search:',
            lengthMenu: '_MENU_ per page',
            info: 'Showing _START_ to _END_ of _TOTAL_ users',
            emptyTable: 'No duplicate leads found'
        },
        order: [[4, 'desc']],
        columnDefs: [
            { orderable: false, targets: -1 }
        ],
        buttons: [
            { extend: 'excel', text: '<i class="bx bx-spreadsheet me-1"></i> Excel', className: 'btn btn-sm btn-outline-success me-1' },
            { extend: 'pdf',   text: '<i class="bx bx-file me-1"></i> PDF',   className: 'btn btn-sm btn-outline-danger' }
        ]
    });
});
</script>
@endsection

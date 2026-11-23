@extends('layouts/contentNavbarLayout')

@section('title', 'Employees')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
  Employees
</h4>

@if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Employee Report</h5>
        @can('employee:create')
        <a href="{{ route('employee.create') }}" class="btn btn-primary">
            <i class='bx bx-plus'></i> Add Employee
        </a>
        @endcan
    </div>

    <div class="card-body">
        <!-- Search & Filters -->
        <div class="row g-3 mb-3">
            <div class="col-12 col-md-3">
                <label class="form-label">Search</label>
                <input type="text" id="searchInput" class="form-control" placeholder="Name, Code, Email, Mobile..." value="">
                <small class="text-muted">Search by name, emp code, email or mobile</small>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label">Company</label>
                <select id="filterCompany" class="form-select">
                    <option value="">All Companies</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label">Segment</label>
                <select id="filterSegment" class="form-select">
                    <option value="">All Segments</option>
                    @foreach($segments as $segment)
                        <option value="{{ $segment->id }}">{{ $segment->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label">Designation</label>
                <select id="filterDesignation" class="form-select">
                    <option value="">All Designations</option>
                    @foreach($designations as $designation)
                        <option value="{{ $designation->id }}">{{ $designation->designation_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label">Status</label>
                <select id="filterStatus" class="form-select">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="d-flex gap-2">
                    <button type="button" id="applyFilters" class="btn btn-primary" title="Filter">
                        <i class='bx bx-filter'></i> Filter
                    </button>
                    <button type="button" id="exportEmployees" class="btn btn-success" title="Export">
                        <i class='bx bx-export'></i> Export
                    </button>
                    <button type="button" id="resetFilters" class="btn btn-secondary" title="Reset">
                        <i class='bx bx-reset'></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Emp Code</th>
                    <th>Email</th>
                    <th>Name</th>
                    <th>Mobile Number</th>
                    <th>Designation</th>
                    <th>Company</th>
                    <th>Segment</th>
                    <th>Reports To</th>
                    <th>Status</th>
                    <th width="120">Actions</th>
                </tr>
            </thead>
            <tbody id="employeesTableBody">
                <tr>
                    <td colspan="11" class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="card-footer" id="paginationContainer"></div>
</div>
@endsection

@section('page-style')
<style>
.table th { white-space: nowrap; }
</style>
@endsection

@section('page-script')
<script>
(function checkJQuery() {
    if (typeof jQuery !== 'undefined') {
        initializePage();
    } else {
        setTimeout(checkJQuery, 50);
    }
})();

function initializePage() {
    let currentPage = 1;

    function loadEmployees(page = 1) {
        const formData = {
            search: $('#searchInput').val(),
            company_id: $('#filterCompany').val(),
            segment_id: $('#filterSegment').val(),
            designation_id: $('#filterDesignation').val(),
            employee_status: $('#filterStatus').val(),
            page: page
        };

        $('#employeesTableBody').html(`
            <tr>
                <td colspan="11" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </td>
            </tr>
        `);

        $.ajax({
            url: '{{ route("employee.data") }}',
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#employeesTableBody').html(response.html);
                    $('#paginationContainer').html(response.pagination);
                    currentPage = page;
                    attachPaginationHandlers();
                }
            },
            error: function(xhr) {
                $('#employeesTableBody').html(`
                    <tr>
                        <td colspan="11" class="text-center text-danger">
                            Error loading data. Please try again.
                        </td>
                    </tr>
                `);
                $('#paginationContainer').html('');
            }
        });
    }

    function attachPaginationHandlers() {
        $('#paginationContainer').off('click', 'a').on('click', 'a', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            if (url) {
                const match = url.match(/page=(\d+)/);
                const page = match ? parseInt(match[1], 10) : 1;
                loadEmployees(page);
            }
        });
    }

    $(document).ready(function() {
        loadEmployees(1);

        $('#exportEmployees').on('click', function() {
            const params = new URLSearchParams({
                search: $('#searchInput').val() || '',
                company_id: $('#filterCompany').val() || '',
                segment_id: $('#filterSegment').val() || '',
                designation_id: $('#filterDesignation').val() || '',
                employee_status: $('#filterStatus').val() || ''
            });
            window.location.href = '{{ route("employee.export") }}?' + params.toString();
        });

        $('#applyFilters').on('click', function() {
            loadEmployees(1);
        });

        $('#resetFilters').on('click', function() {
            $('#searchInput').val('');
            $('#filterCompany, #filterSegment, #filterDesignation, #filterStatus').val('');
            loadEmployees(1);
        });

        $('#searchInput').on('keypress', function(e) {
            if (e.which === 13) {
                loadEmployees(1);
            }
        });
    });
}
</script>
@endsection

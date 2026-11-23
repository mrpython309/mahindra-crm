@extends('layouts/contentNavbarLayout')

@section('title', 'Colours')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
@endsection

@section('page-style')
    <style>
        .avatar-initial {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            color: white;
        }
        .fs-12px { font-size: 0.75rem !important; }
    </style>
@endsection

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-0">Colours</h4>
      </div>
    </div>

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

    {{-- Stats Cards --}}
    <div class="row mb-4 g-3">
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bx bx-check-circle text-success bx-lg mb-2"></i>
                    <h3 class="text-success mb-1">{{ $activeColours }}</h3>
                    <small class="text-muted fw-semibold">Active</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bx bx-pause-circle text-warning bx-lg mb-2"></i>
                    <h3 class="text-warning mb-1">{{ $inactiveColours }}</h3>
                    <small class="text-muted fw-semibold">Inactive</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bx bx-palette bx-lg mb-2 text-primary"></i>
                    <h3 class="mb-1">{{ $colours->total() }}</h3>
                    <small class="text-muted fw-semibold">Total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <a href="{{ route('colours.create') }}" class="btn btn-primary w-100">
                        <i class="bx bx-plus me-1"></i>Add Colour
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- DataTable --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Colours List</h5>
            <div class="d-flex gap-2">
                @can('colours:create')
                    <a href="{{ route('colours.import.template') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bx bx-download me-1"></i> Sample CSV
                    </a>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#colourImportForm" aria-expanded="false" aria-controls="colourImportForm">
                        <i class="bx bx-upload me-1"></i> Bulk Import
                    </button>
                @endcan
                <button class="btn btn-outline-primary btn-sm" id="exportBtn">
                    <i class="bx bx-export me-1"></i> Export
                </button>
                <a href="{{ route('colours.create') }}" class="btn btn-primary btn-sm">
                    <i class="bx bx-plus me-1"></i> Add Colour
                </a>
            </div>
        </div>
        @can('colours:create')
            <div class="collapse border-top" id="colourImportForm">
                <div class="card-body">
                    <form method="POST" action="{{ route('colours.import') }}" enctype="multipart/form-data" class="row g-3 align-items-end">
                        @csrf
                        <div class="col-md-6 col-lg-4">
                            <label class="form-label" for="colourImportFile">Select CSV file</label>
                            <input id="colourImportFile" type="file" name="file" accept=".csv,.txt" class="form-control" required>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <button type="submit" class="btn btn-primary mt-3 mt-md-0">
                                <i class="bx bx-upload me-1"></i> Upload & Import
                            </button>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">Only CSV files from the Sample CSV template are supported (columns: name, status).</small>
                        </div>
                    </form>
                </div>
            </div>
        @endcan
        <div class="table-responsive">
            <table class="table card-table table-striped table-hover" id="coloursTable">
                <thead class="table-light">
                    <tr>
                        <th>Colour</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($colours as $colour)
                        @php
                            $colorMap = [
                                'red' => '#e31837',
                                'crimson' => '#e31837',
                                'black' => '#1c1c1f',
                                'white' => '#ffffff',
                                'silver' => '#d1d1d6',
                                'grey' => '#8e8e93',
                                'gray' => '#8e8e93',
                                'blue' => '#0056b3',
                                'gold' => '#ffcc00',
                                'bronze' => '#a2845e',
                                'beige' => '#e5d3b3',
                                'copper' => '#b25038',
                                'orange' => '#ff9500',
                                'green' => '#1e7e34',
                                'brown' => '#9c7c5d'
                            ];
                            $normName = strtolower(trim($colour->name));
                            $hex = '#8592a3'; // default bootstrap grey
                            foreach ($colorMap as $key => $val) {
                                if (strpos($normName, $key) !== false) {
                                    $hex = $val;
                                    break;
                                }
                            }
                            $isLightColor = ($hex === '#ffffff' || $hex === '#d1d1d6' || $hex === '#e5d3b3');
                            $borderStyle = $isLightColor ? 'border: 1px solid rgba(28, 28, 31, 0.15) !important;' : '';
                            $textColor = $isLightColor ? '#1c1c1f' : '#ffffff';
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        <div class="avatar-initial rounded-circle" style="background-color: {{ $hex }} !important; color: {{ $textColor }} !important; {{ $borderStyle }} font-weight: 600;">
                                            {{ strtoupper(substr($colour->name, 0, 2)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $colour->name }}</div>
                                        <small class="text-muted">ID: {{ $colour->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($colour->status === 'active')
                                    <span class="badge bg-label-success">Active</span>
                                @else
                                    <span class="badge bg-label-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn p-0 btn-sm" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="{{ route('colours.show', $colour) }}">
                                            <i class="bx bx-show me-1"></i>View</a></li>
                                        <li><a class="dropdown-item" href="{{ route('colours.edit', $colour) }}">
                                            <i class="bx bx-edit me-1"></i>Edit</a></li>
                                        <li>
                                            <form method="POST" action="{{ route('colours.destroy', $colour) }}" class="d-inline delete-form">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bx bx-trash me-2"></i>Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-5">
                                <i class="bx bx-palette bx-lg text-muted mb-3"></i>
                                <p class="text-muted mb-2">No colours configured</p>
                                <a href="{{ route('colours.create') }}" class="btn btn-primary btn-sm">
                                    <i class="bx bx-plus me-1"></i>Add First Colour
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            @if($colours->hasPages())
                <div class="text-muted">
                    Showing {{ $colours->firstItem() ?? 0 }} to {{ $colours->lastItem() ?? 0 }} of {{ $colours->total() }} colours
                </div>
                {{ $colours->links() }}
            @endif
        </div>
    </div>
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});

$(function() {
    const table = $('#coloursTable').DataTable({
        pageLength: 15,
        responsive: true,
        order: [[0, 'asc']],
        columnDefs: [{ orderable: false, targets: -1 }],
        dom: 'Bfrtip',
        language: { lengthMenu: "_MENU_ per page" },
        buttons: ['excel', 'pdf']
    });

    $('#exportBtn').on('click', function() {
        table.buttons().trigger();
    });
});
</script>
@endsection

@extends('layouts/contentNavbarLayout')

@section('title', 'Leads')

@section('content')
@php
    $isSuperAdminView = auth()->user()->hasRole('superadmin');
    $isTeleCallerView = auth()->user()->hasRole('tele-caller') && !$isSuperAdminView;
    $showIdColumn = !$isTeleCallerView;
    $showCompanyColumn = !$isTeleCallerView;
    $showTeleCallerColumn = !$isTeleCallerView;
    $canAssignLeads = auth()->user()->can('leads:assign');
    $leadTableColumnCount = $isTeleCallerView
        ? ($canAssignLeads ? 11 : 10)
        : ($canAssignLeads ? 14 : 13);
@endphp
<h4 class="py-3 breadcrumb-wrapper mb-4">
    Leads Management
</h4>

@if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        @can('leads:create')
            @if(session('import_batch_id'))
                <div class="mt-2">
                    <a href="{{ route('leads.import.logs', ['batch' => session('import_batch_id')]) }}" class="alert-link">View detailed log for this import</a>
                </div>
            @endif
        @endcan
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Lead List</h5>
        <div class="d-flex gap-2 align-items-center">
            @if(auth()->user()->hasRole('superadmin'))
            <a href="{{ route('leads.export') }}" id="exportLeadsBtn" class="btn btn-outline-success btn-sm">
                <i class="bx bx-export me-1"></i> Export Leads
            </a>
            @endif
            @can('leads:create')
            <a href="{{ route('leads.import.template') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-download me-1"></i> Sample CSV
            </a>
            <a href="{{ route('leads.import.logs') }}" class="btn btn-outline-info btn-sm" title="Skipped rows, validation errors, and summary per import">
                <i class="bx bx-list-ul me-1"></i> Import logs
            </a>
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#leadImportForm" aria-expanded="false" aria-controls="leadImportForm">
                <i class="bx bx-upload me-1"></i> Bulk Import
            </button>
            <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="collapse" data-bs-target="#userDataUploadForm" aria-expanded="false" aria-controls="userDataUploadForm">
                <i class="bx bx-cloud-upload me-1"></i> Upload User Data
            </button>
            <a href="{{ route('leads.create') }}" class="btn btn-primary btn-sm">
                <i class="bx bx-plus"></i> Add Lead
            </a>
            @endcan
        </div>
    </div>

    @can('leads:create')
    <div class="collapse border-top" id="leadImportForm">
        <div class="card-body border-bottom">
            <form method="POST" action="{{ route('leads.import') }}" enctype="multipart/form-data" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-6 col-lg-4">
                    <label class="form-label" for="leadImportFile">Select CSV file</label>
                    <input id="leadImportFile" type="file" name="file" accept=".csv" class="form-control" required>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-upload me-1"></i> Upload & Import
                    </button>
                </div>
                <div class="col-12">
                    <small class="text-muted">
                        Only CSV files matching the Sample CSV template format are supported.
                        Include <strong>lead_date</strong> in <code>YYYY-MM-DD</code> format (future dates are not allowed).
                    </small>
                </div>
            </form>
        </div>
    </div>

    <div class="collapse border-top" id="userDataUploadForm">
        <div class="card-body border-bottom">
            <form method="POST" action="{{ route('leads.user-data.upload') }}" enctype="multipart/form-data" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-4">
                    <label class="form-label" for="userDataLeadResponse">Lead Response <span class="text-danger">*</span></label>
                    <select id="userDataLeadResponse" name="status_source_id" class="form-select" required>
                        <option value="">-- Select Lead Response --</option>
                        @foreach($statusSources as $source)
                            <option value="{{ $source->id }}">
                                {{ $source->response }}{{ $source->leadStatus ? ' (' . $source->leadStatus->name . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="userDataFile">Select CSV file <span class="text-danger">*</span></label>
                    <input id="userDataFile" type="file" name="file" accept=".csv" class="form-control" required>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-warning">
                        <i class="bx bx-cloud-upload me-1"></i> Upload & Update
                    </button>
                </div>
                <div class="col-12">
                    <small class="text-muted">
                        Matches leads by mobile number and vehicle model, then updates their lead response.
                        <a href="{{ route('leads.user-data.template') }}" class="ms-1">
                            <i class="bx bx-download me-1"></i>Download sample file
                        </a>
                    </small>
                </div>
            </form>
        </div>
    </div>
    @endcan

    <div class="card-body">
        <!-- Search & Filters -->
        <div class="row g-3 mb-3">
            <div class="col-12 col-md-3">
                <label class="form-label">Search</label>
                <input type="text" id="searchInput" class="form-control" placeholder="Name, Mobile, City...">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label">Lead Type</label>
                <select id="filterType" class="form-select">
                    <option value="">All Types</option>
                    @foreach($leadTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label">Source</label>
                <select id="filterSource" class="form-select">
                    <option value="">All Sources</option>
                    @foreach($leadSources as $source)
                        <option value="{{ $source->id }}">{{ $source->campaign }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label">Lead Response</label>
                <select id="filterLeadResponse" class="form-select">
                    <option value="">All Responses</option>
                    @foreach($statusSources as $source)
                        <option value="{{ $source->id }}">
                            {{ $source->response }}{{ $source->leadStatus ? ' (' . $source->leadStatus->name . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label">Enquiry Type</label>
                <select id="filterEnquiryType" class="form-select">
                    <option value="">All Enquiry Types</option>
                    @foreach($enquiryTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" for="filterDateFrom">Date from</label>
                <input type="date" id="filterDateFrom" class="form-control" title="Filter by lead date (same as DATE column)">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" for="filterDateTo">Date to</label>
                <input type="date" id="filterDateTo" class="form-control" title="Filter by lead date (same as DATE column)">
            </div>
            <div class="col-12 col-md-auto d-flex align-items-end">
                <div class="d-flex gap-2">
                    <button type="button" id="applyFilters" class="btn btn-primary" title="Filter">
                        <i class='bx bx-filter'></i> Filter
                    </button>
                    <button type="button" id="resetFilters" class="btn btn-secondary" title="Reset">
                        <i class='bx bx-reset'></i> Reset
                    </button>
                </div>
            </div>
            @can('leads:assign')
            <div class="col-12 col-md-auto d-flex align-items-end">
                <div class="d-flex gap-2 align-items-center">
                    <button type="button" id="openBulkAssignBtn" class="btn btn-outline-primary" disabled>
                        <i class='bx bx-user-check me-1'></i> Bulk Assign/Reassign
                    </button>
                    <small id="bulkSelectionCount" class="text-muted">0 selected</small>
                </div>
            </div>
            @endcan
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-sm lead-list-table">
            <thead>
                <tr>
                    @can('leads:assign')
                    <th width="40">
                        <input type="checkbox" class="form-check-input" id="selectAllLeads">
                    </th>
                    @endcan
                    @if($showIdColumn)
                    <th>ID</th>
                    @endif
                    <th>TYPE</th>
                    <th>SOURCE</th>
                    <th>DATE</th>
                    <th>NAME</th>
                    <th>MOBILE</th>
                    @if($showCompanyColumn)
                    <th>COMPANY</th>
                    @endif
                    <th>CITY</th>
                    <th>NEXT FOLLOW UP</th>
                    @if($showTeleCallerColumn)
                    <th>TELE-CALLER</th>
                    @endif
                    <th>SALES CONSULTANT</th>
                    <th>STATUS</th>
                    <th width="80">ACTION</th>
                </tr>
            </thead>
            <tbody id="leadsTableBody">
                <tr>
                    <td colspan="{{ $leadTableColumnCount }}" class="text-center">
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

@can('leads:assign')
    @include('lead.partials.assign-modal', ['companies' => $companies ?? collect()])
@endcan
@endsection

@section('page-style')
<style>
/* Keep lead list readable without left-right scrolling. */
.lead-list-table {
    width: 100%;
    table-layout: fixed;
    font-size: 0.82rem;
}

.lead-list-table th,
.lead-list-table td {
    padding: 0.45rem 0.4rem;
    white-space: normal;
    word-break: break-word;
    vertical-align: middle;
}

/* Keep key fields compact and single-line. */
.lead-list-table th:nth-child(2),
.lead-list-table td:nth-child(2),
.lead-list-table th:nth-child(5),
.lead-list-table td:nth-child(5),
.lead-list-table th:nth-child(7),
.lead-list-table td:nth-child(7),
.lead-list-table th:last-child,
.lead-list-table td:last-child {
    white-space: nowrap;
}
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
    const isSuperAdmin = {{ auth()->user()->hasRole('superadmin') ? 'true' : 'false' }};
    const isTeleCaller = {{ auth()->user()->hasRole('tele-caller') ? 'true' : 'false' }};
    const canAssign = {{ auth()->user()->can('leads:assign') ? 'true' : 'false' }};
    const currentUserCompanyId = '{{ auth()->user()->employee->company_id ?? '' }}';
    const currentUserName = @json(auth()->user()->name ?? '');
    const tableColumnCount = {{ $leadTableColumnCount }};

    function resetBulkSelection() {
        if (!canAssign) return;
        $('#selectAllLeads').prop('checked', false);
        $('.bulk-lead-checkbox').prop('checked', false);
        updateBulkSelectionState();
    }

    function getSelectedLeadRows() {
        if (!canAssign) return [];
        return $('.bulk-lead-checkbox:checked').map(function () {
            return {
                id: parseInt($(this).val(), 10),
                hasTeleCaller: String($(this).data('has-tele-caller')) === '1',
            };
        }).get();
    }

    function updateBulkSelectionState() {
        if (!canAssign) return;
        const selectedCount = $('.bulk-lead-checkbox:checked').length;
        $('#bulkSelectionCount').text(selectedCount + ' selected');
        $('#openBulkAssignBtn').prop('disabled', selectedCount === 0);
    }

    function loadBulkTeleCallers() {
        $('#assignTeleCallerId').html('<option value="">Loading tele-callers...</option>').prop('disabled', true);
        $.get('{{ route("leads.ajax.tele-callers") }}', function (data) {
            $('#assignTeleCallerId').html('<option value="">Select Tele-caller</option>').prop('disabled', false);
            (data || []).forEach(function (caller) {
                $('#assignTeleCallerId').append(
                    '<option value="' + caller.id + '">' + caller.name + ' (' + (caller.email || '') + ')</option>'
                );
            });
        }).fail(function () {
            $('#assignTeleCallerId').html('<option value="">Error loading tele-callers</option>').prop('disabled', true);
            $('#assignModalError').text('Unable to load tele-callers. Please try again.').removeClass('d-none');
        });
    }

    function loadLeads(page) {
        page = page || 1;
        var formData = {
            search:          $('#searchInput').val(),
            lead_type_id:    $('#filterType').val(),
            lead_source_id:  $('#filterSource').val(),
            lead_response_id: $('#filterLeadResponse').val(),
            enquiry_type_id:  $('#filterEnquiryType').val(),
            date_from:       $('#filterDateFrom').val(),
            date_to:         $('#filterDateTo').val(),
            page:            page
        };

        $('#leadsTableBody').html(
            '<tr><td colspan="' + tableColumnCount + '" class="text-center">' +
            '<div class="spinner-border text-primary" role="status">' +
            '<span class="visually-hidden">Loading...</span></div></td></tr>'
        );

        $.ajax({
            url: '{{ route("leads.data") }}',
            type: 'POST',
            data: formData,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                if (response.success) {
                    $('#leadsTableBody').html(response.html);
                    $('#paginationContainer').html(response.pagination);
                    resetBulkSelection();
                    attachPaginationHandlers();
                }
            },
            error: function () {
                $('#leadsTableBody').html(
                    '<tr><td colspan="' + tableColumnCount + '" class="text-center text-danger">Error loading data. Please try again.</td></tr>'
                );
                $('#paginationContainer').html('');
                resetBulkSelection();
            }
        });
    }

    function attachPaginationHandlers() {
        $('#paginationContainer').off('click', 'a').on('click', 'a', function (e) {
            e.preventDefault();
            var url   = $(this).attr('href');
            var match = url ? url.match(/page=(\d+)/) : null;
            loadLeads(match ? parseInt(match[1], 10) : 1);
        });
    }

    $(document).ready(function () {
        loadLeads(1);

        $('#exportLeadsBtn').on('click', function (e) {
            e.preventDefault();

            const params = new URLSearchParams();
            const search = $('#searchInput').val();
            const leadTypeId = $('#filterType').val();
            const leadSourceId = $('#filterSource').val();
            const leadResponseId = $('#filterLeadResponse').val();
            const enquiryTypeId = $('#filterEnquiryType').val();
            const dateFrom = $('#filterDateFrom').val();
            const dateTo = $('#filterDateTo').val();

            if (search) params.append('search', search);
            if (leadTypeId) params.append('lead_type_id', leadTypeId);
            if (leadSourceId) params.append('lead_source_id', leadSourceId);
            if (leadResponseId) params.append('lead_response_id', leadResponseId);
            if (enquiryTypeId) params.append('enquiry_type_id', enquiryTypeId);
            if (dateFrom) params.append('date_from', dateFrom);
            if (dateTo) params.append('date_to', dateTo);

            const baseUrl = '{{ route("leads.export") }}';
            const query = params.toString();
            window.location.href = query ? `${baseUrl}?${query}` : baseUrl;
        });

        $('#applyFilters').on('click', function () {
            loadLeads(1);
        });

        $('#resetFilters').on('click', function () {
            $('#searchInput').val('');
            $('#filterType, #filterSource, #filterLeadResponse, #filterEnquiryType').val('');
            $('#filterDateFrom, #filterDateTo').val('');
            loadLeads(1);
        });

        $('#searchInput').on('keypress', function (e) {
            if (e.which === 13) loadLeads(1);
        });

        $(document).on('change', '#selectAllLeads', function () {
            $('.bulk-lead-checkbox').prop('checked', $(this).is(':checked'));
            updateBulkSelectionState();
        });

        $(document).on('change', '.bulk-lead-checkbox', function () {
            const total = $('.bulk-lead-checkbox').length;
            const checked = $('.bulk-lead-checkbox:checked').length;
            $('#selectAllLeads').prop('checked', total > 0 && total === checked);
            updateBulkSelectionState();
        });

        $(document).on('click', '#openBulkAssignBtn', function () {
            const selectedLeads = getSelectedLeadRows();
            if (!selectedLeads.length) {
                toastr.error('Please select at least one lead.');
                return;
            }

            const hasAssigned = selectedLeads.some(function (lead) { return lead.hasTeleCaller; });
            const hasUnassigned = selectedLeads.some(function (lead) { return !lead.hasTeleCaller; });
            if (hasAssigned && hasUnassigned) {
                toastr.error('Select either only unassigned leads (assign) or only assigned leads (reassign).');
                return;
            }

            const mode = hasAssigned ? 'reassign' : 'assign';
            $('#assignActionMode').val(mode);
            $('#assignLeadIds').val(selectedLeads.map(function (lead) { return lead.id; }).join(','));
            $('#assignLeadId').val('');
            $('#assignModalError').addClass('d-none').text('');
            $('#assignModalNoCompany').addClass('d-none');
            $('#assignModalModeHint').removeClass('d-none').text(
                mode === 'assign'
                    ? 'Bulk assign mode: only unassigned leads are selected.'
                    : 'Bulk reassign mode: only already assigned leads are selected.'
            );

            $('#assignLeadModalLabel').html('<i class="bx bx-user-check me-2"></i>' + (mode === 'assign' ? 'Bulk Assign Tele-caller' : 'Bulk Reassign Tele-caller'));
            $('#assignSubmitBtn').html('<i class="bx bx-check me-1"></i> ' + (mode === 'assign' ? 'Assign Selected' : 'Reassign Selected'));

            $('#assignCompanyWrap, #assignLocationWrap, #assignSalesConsultantWrap').addClass('d-none');
            $('#assignTeleCallerWrap').removeClass('d-none');
            $('#assignCompanyId, #assignLocationId, #assignSalesConsultantId').prop('disabled', true);
            $('#assignTeleCallerName').addClass('d-none').val('');
            $('#assignTeleCallerId').removeClass('d-none').val('');
            $('#assignSubmitBtn').prop('disabled', true);

            loadBulkTeleCallers();
            new bootstrap.Modal(document.getElementById('assignLeadModal')).show();
        });
    });

    // ── Assign Lead Modal ────────────────────────────────────────────────────

    $(document).on('click', '.assign-lead-btn', function (e) {
        e.preventDefault();
        var leadId                = $(this).data('lead-id');
        var leadCompanyId         = $(this).data('lead-company-id') || '';
        var leadLocationId        = $(this).data('lead-location-id') || '';
        var leadTeleCallerId      = $(this).data('lead-tele-caller-id') || '';
        var leadSalesConsultantId = $(this).data('lead-sales-consultant-id') || '';
        var currentUserId         = $(this).data('current-user-id') || '';
        if (!leadId) return;

        $('#assignActionMode').val('single');
        $('#assignLeadIds').val('');
        $('#assignLeadModalLabel').html("<i class='bx bx-user-check me-2'></i>Assign Lead");
        $('#assignSubmitBtn').html("<i class='bx bx-check me-1'></i> Assign");
        $('#assignCompanyWrap, #assignLocationWrap, #assignSalesConsultantWrap, #assignTeleCallerWrap').removeClass('d-none');
        $('#assignModalModeHint').addClass('d-none').text('');

        $('#assignLeadId').val(leadId);
        $('#assignModalError').addClass('d-none').text('');
        $('#assignModalNoCompany').addClass('d-none');
        $('#assignLocationId').html('<option value="">Select Location first</option>').prop('disabled', true);
        $('#assignTeleCallerId').html('<option value="">Select Tele-caller</option>').prop('disabled', true);
        $('#assignSalesConsultantId').html('<option value="">Select Sales Consultant (optional)</option>').prop('disabled', true);
        $('#assignSubmitBtn').prop('disabled', true);
        $('#assignTeleCallerName').addClass('d-none').val('');
        $('#assignTeleCallerId').removeClass('d-none');

        window.assignLeadCurrentUserId = currentUserId;
        window.assignLeadPreload = (leadTeleCallerId || leadSalesConsultantId)
            ? { teleCallerId: leadTeleCallerId, salesConsultantId: leadSalesConsultantId }
            : null;
        window.assignLeadPendingLocationId = leadLocationId || '';

        if (leadCompanyId) {
            $('#assignCompanyId').val(leadCompanyId);
            loadAssignLocations(leadCompanyId);
        } else if (!isSuperAdmin && currentUserCompanyId) {
            $('#assignCompanyId').val(currentUserCompanyId);
            loadAssignLocations(currentUserCompanyId);
        } else {
            $('#assignCompanyId').val('');
            $('#assignModalNoCompany').removeClass('d-none');
        }

        if (!isSuperAdmin && currentUserCompanyId) {
            $('#assignCompanyId').prop('disabled', true);
        } else {
            $('#assignCompanyId').prop('disabled', false);
        }

        if (!isSuperAdmin && isTeleCaller) {
            $('#assignTeleCallerName').val(currentUserName).removeClass('d-none');
            $('#assignTeleCallerId').addClass('d-none').prop('disabled', true).val(currentUserId || '');
        }

        new bootstrap.Modal(document.getElementById('assignLeadModal')).show();
    });

    function loadAssignLocations(companyId) {
        $('#assignModalNoCompany, #assignModalError').addClass('d-none');
        $('#assignLocationId').html('<option value="">Select Location</option>').prop('disabled', !companyId);
        $('#assignTeleCallerId').html('<option value="">Select Tele-caller</option>').prop('disabled', true);
        $('#assignSalesConsultantId').html('<option value="">Select Sales Consultant (optional)</option>').prop('disabled', true);
        $('#assignSubmitBtn').prop('disabled', true);
        window.assignLeadPendingLocationId = window.assignLeadPendingLocationId || '';
        if (companyId) {
            $('#assignLocationId').html('<option value="">Loading...</option>');
            $.get('{{ url("leads/ajax/locations") }}/' + companyId, function (data) {
                $('#assignLocationId').html('<option value="">Select Location</option>').prop('disabled', false);
                data.forEach(function (loc) {
                    $('#assignLocationId').append(
                        '<option value="' + loc.id + '">' + loc.name + (loc.state ? ', ' + loc.state : '') + '</option>'
                    );
                });

                var pendingLocationId = window.assignLeadPendingLocationId ? String(window.assignLeadPendingLocationId) : '';
                var selectedLocationId = '';
                if (pendingLocationId && $('#assignLocationId option[value="' + pendingLocationId + '"]').length) {
                    selectedLocationId = pendingLocationId;
                } else if ((data || []).length > 0) {
                    // Default to the first available location so assignees can load immediately.
                    selectedLocationId = String(data[0].id);
                }

                window.assignLeadPendingLocationId = '';
                if (selectedLocationId) {
                    $('#assignLocationId').val(selectedLocationId);
                    loadAssignableUsers(selectedLocationId);
                }
            }).fail(function () {
                $('#assignLocationId').html('<option value="">Error loading locations</option>');
                $('#assignModalError').text('Unable to load locations for selected company.').removeClass('d-none');
            });
        }
    }

    function loadAssignableUsers(locationId) {
        $('#assignTeleCallerId').html('<option value="">Select Tele-caller</option>').prop('disabled', !locationId || (!isSuperAdmin && isTeleCaller));
        $('#assignSalesConsultantId').html('<option value="">Select Sales Consultant (optional)</option>').prop('disabled', !locationId);
        $('#assignSubmitBtn').prop('disabled', true);

        if (!locationId) return;

        $('#assignTeleCallerId, #assignSalesConsultantId').html('<option value="">Loading...</option>');
        $.get('{{ url("leads/ajax/assignable-users") }}/' + locationId, function (data) {
            $('#assignTeleCallerId').html('<option value="">Select Tele-caller</option>').prop('disabled', !isSuperAdmin && isTeleCaller);
            (data.tele_callers || []).forEach(function (c) {
                $('#assignTeleCallerId').append('<option value="' + c.id + '">' + c.name + ' (' + (c.email || '') + ')</option>');
            });
            $('#assignSalesConsultantId').html('<option value="">Select Sales Consultant (optional)</option>').prop('disabled', false);
            (data.sales_consultants || []).forEach(function (c) {
                $('#assignSalesConsultantId').append('<option value="' + c.id + '">' + c.name + ' (' + (c.email || '') + ')</option>');
            });

            var shouldPreload = window.assignLeadPreload && (isSuperAdmin || (window.assignLeadCurrentUserId && String(window.assignLeadPreload.teleCallerId) === String(window.assignLeadCurrentUserId)));
            if (shouldPreload) {
                if (window.assignLeadPreload.teleCallerId) $('#assignTeleCallerId').val(window.assignLeadPreload.teleCallerId);
                if (window.assignLeadPreload.salesConsultantId) $('#assignSalesConsultantId').val(window.assignLeadPreload.salesConsultantId);
                $('#assignTeleCallerId, #assignSalesConsultantId').trigger('change');
                window.assignLeadPreload = null;
            }

            if (!isSuperAdmin && isTeleCaller && window.assignLeadCurrentUserId) {
                $('#assignTeleCallerId').val(window.assignLeadCurrentUserId);
                $('#assignTeleCallerName').val(currentUserName).removeClass('d-none');
                $('#assignTeleCallerId').addClass('d-none').prop('disabled', true);
            }
        }).fail(function () {
            $('#assignTeleCallerId').html('<option value="">Error loading tele-callers</option>');
            $('#assignSalesConsultantId').html('<option value="">Error loading sales consultants</option>');
        });
    }

    // Company → Location cascade
    $(document).on('change', '#assignCompanyId', function () {
        loadAssignLocations($(this).val());
    });

    // Location → Tele-caller & Sales Consultant cascade
    $(document).on('change', '#assignLocationId', function () {
        loadAssignableUsers($(this).val());
    });

    // Enable submit button when required fields are filled
    $(document).on('change', '#assignCompanyId, #assignLocationId, #assignTeleCallerId, #assignSalesConsultantId', function () {
        var actionMode = $('#assignActionMode').val() || 'single';
        if (actionMode !== 'single') {
            $('#assignSubmitBtn').prop('disabled', !$('#assignTeleCallerId').val());
            return;
        }

        var hasCompany        = $('#assignCompanyId').val();
        var hasLocation       = $('#assignLocationId').val();
        var teleCallerId      = $('#assignTeleCallerId').val();
        var salesConsultantId = $('#assignSalesConsultantId').val();
        var ok = isSuperAdmin
            ? !!(hasCompany && hasLocation && teleCallerId)
            : !!(hasCompany && hasLocation && salesConsultantId);
        $('#assignSubmitBtn').prop('disabled', !ok);
    });

    // Assign submit
    $(document).on('click', '#assignSubmitBtn', function () {
        var actionMode        = $('#assignActionMode').val() || 'single';
        var leadId            = $('#assignLeadId').val();
        var leadIds           = ($('#assignLeadIds').val() || '').split(',').map(function (value) {
            return value.trim();
        }).filter(function (value) { return value.length > 0; });
        var companyId         = $('#assignCompanyId').val();
        var locationId        = $('#assignLocationId').val();
        var teleCallerId      = $('#assignTeleCallerId').val();
        var salesConsultantId = $('#assignSalesConsultantId').val();

        if (actionMode === 'single') {
            if (!companyId) { $('#assignModalError').text('Please select a company.').removeClass('d-none'); return; }
            if (!leadId || !locationId) return;
        } else {
            if (!leadIds.length) { $('#assignModalError').text('Please select leads for bulk action.').removeClass('d-none'); return; }
            if (!teleCallerId) { $('#assignModalError').text('Please select a tele-caller.').removeClass('d-none'); return; }
        }

        var $btn = $(this).prop('disabled', true);
        $('#assignModalError').addClass('d-none');
        if (actionMode === 'single') {
            $.ajax({
                url: '{{ url("leads") }}/' + leadId + '/assign',
                type: 'POST',
                data: {
                    _token:              $('meta[name="csrf-token"]').attr('content'),
                    company_id:          companyId,
                    location_id:         locationId,
                    tele_caller_id:      teleCallerId,
                    sales_consultant_id: salesConsultantId,
                },
                success: function (res) {
                    if (res.success) {
                        bootstrap.Modal.getInstance(document.getElementById('assignLeadModal')).hide();
                        loadLeads(1);
                        toastr.success(res.message);
                    } else {
                        $('#assignModalError').text(res.message || 'Assign failed').removeClass('d-none');
                        $btn.prop('disabled', false);
                    }
                },
                error: function (xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Unable to assign. Please try again.';
                    $('#assignModalError').text(msg).removeClass('d-none');
                    $btn.prop('disabled', false);
                }
            });
            return;
        }

        $.ajax({
            url: '{{ route("leads.bulk-assign") }}',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                lead_ids: leadIds,
                tele_caller_id: teleCallerId,
                mode: actionMode,
            },
            success: function (res) {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('assignLeadModal')).hide();
                    loadLeads(1);
                    toastr.success(res.message);
                } else {
                    $('#assignModalError').text(res.message || 'Bulk assignment failed').removeClass('d-none');
                    $btn.prop('disabled', false);
                }
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Unable to process bulk assignment. Please try again.';
                $('#assignModalError').text(msg).removeClass('d-none');
                $btn.prop('disabled', false);
            }
        });
    });
}
</script>
@endsection

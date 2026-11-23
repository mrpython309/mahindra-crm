@extends('layouts/contentNavbarLayout')

@section('title', 'Lead Details')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
  <span class="text-muted fw-light">Leads /</span> Lead Details
</h4>

@if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Lead Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h5 class="mb-1">{{ $lead->first_name }} {{ $lead->last_name }}</h5>
                <p class="mb-2 text-muted">
                    {{ $lead->mobile_no ?? '-' }}
                    @if($lead->company) @ {{ $lead->company?->name }} @endif
                </p>
                <div class="d-flex gap-2 flex-wrap">
                    @if($lead->leadType)
                        <span class="badge bg-label-primary">{{ $lead->leadType->name }}</span>
                    @endif
                    @if($lead->leadSource)
                        <span class="badge bg-label-info">{{ $lead->leadSource->campaign }}</span>
                    @endif
                    {{-- Pipeline lead stage (same column as lead list "LEAD STAGE") --}}
                    @if($lead->leadStatus)
                        <span class="badge bg-label-primary" title="Lead stage">{{ $lead->leadStatus->name }}</span>
                    @else
                        <span class="text-muted" title="Lead stage">-</span>
                    @endif
                    @include('lead.partials.record-status-badge', ['lead' => $lead])
                </div>
            </div>
            <div class="d-flex gap-2">
                @can('leads:edit')
                <a href="{{ route('leads.edit', $lead->id) }}" class="btn btn-primary btn-sm">
                    <i class='bx bx-edit'></i> Edit
                </a>
                @endcan
                @can('leads:assign')
                <button type="button" class="btn btn-outline-primary btn-sm assign-lead-btn" data-lead-id="{{ $lead->id }}" data-lead-company-id="{{ $lead->company_id ?? '' }}" data-lead-location-id="{{ $lead->location_id ?? '' }}" data-lead-tele-caller-id="{{ $lead->primaryTeleCaller()?->id ?? '' }}" data-lead-sales-consultant-id="{{ $lead->primarySalesConsultant()?->id ?? '' }}" data-current-user-id="{{ auth()->id() }}">
                    <i class='bx bx-user-check'></i> Assign Lead
                </button>
                @endcan
                <a href="{{ route('leads.index') }}" class="btn btn-secondary btn-sm">
                    <i class='bx bx-arrow-back'></i> Back
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Tabbed Content -->
<div class="card">
    <div class="card-body">
        <ul class="nav nav-tabs" role="tablist">
            @can('leads:view')
            <li class="nav-item">
                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#profile" aria-controls="profile" aria-selected="true">
                    <i class='bx bx-user'></i> Profile
                </button>
            </li>
            @endcan
            @can('leads:attachments:view')
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#attachments" aria-controls="attachments" aria-selected="false">
                    <i class='bx bx-paperclip'></i> Attachments
                </button>
            </li>
            @endcan
            @can('leads:calls:view')
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#calls" aria-controls="calls" aria-selected="false">
                    <i class='bx bx-phone'></i> Follow-ups
                </button>
            </li>
            @endcan
            @can('leads:activities:view')
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#assignment-history" aria-controls="assignment-history" aria-selected="false">
                    <i class='bx bx-user-check'></i> Assignment History
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#activities" aria-controls="activities" aria-selected="false">
                    <i class='bx bx-list-ul'></i> Activities
                </button>
            </li>
            @endcan
            @can('leads:notes:view')
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#notes" aria-controls="notes" aria-selected="false">
                    <i class='bx bx-note'></i> Notes
                </button>
            </li>
            @endcan
            @can('leads:tasks:view')
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tasks" aria-controls="tasks" aria-selected="false">
                    <i class='bx bx-task'></i> Tasks
                </button>
            </li>
            @endcan
            @can('leads:reminders:view')
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#reminders" aria-controls="reminders" aria-selected="false">
                    <i class='bx bx-bell'></i> Reminders
                </button>
            </li>
            @endcan
            @can('leads:tags:view')
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tags" aria-controls="tags" aria-selected="false">
                    <i class='bx bx-purchase-tag'></i> Tags
                </button>
            </li>
            @endcan
        </ul>

        <div class="tab-content mt-3">
            @can('leads:view')
            <div class="tab-pane fade show active" id="profile" role="tabpanel">
                @include('lead.partials.profile')
            </div>
            @endcan
            @can('leads:attachments:view')
            <div class="tab-pane fade" id="attachments" role="tabpanel">
                @include('lead.partials.attachments')
            </div>
            @endcan
            @can('leads:calls:view')
            <div class="tab-pane fade" id="calls" role="tabpanel">
                @include('lead.partials.calls')
            </div>
            @endcan
            @can('leads:activities:view')
            <div class="tab-pane fade" id="assignment-history" role="tabpanel">
                @include('lead.partials.assignment-history')
            </div>
            <div class="tab-pane fade" id="activities" role="tabpanel">
                @include('lead.partials.activities')
            </div>
            @endcan
            @can('leads:notes:view')
            <div class="tab-pane fade" id="notes" role="tabpanel">
                @include('lead.partials.notes')
            </div>
            @endcan
            @can('leads:tasks:view')
            <div class="tab-pane fade" id="tasks" role="tabpanel">
                @include('lead.partials.tasks')
            </div>
            @endcan
            @can('leads:reminders:view')
            <div class="tab-pane fade" id="reminders" role="tabpanel">
                @include('lead.partials.reminders')
            </div>
            @endcan
            @can('leads:tags:view')
            <div class="tab-pane fade" id="tags" role="tabpanel">
                @include('lead.partials.tags')
            </div>
            @endcan
        </div>
    </div>
</div>

@can('leads:assign')
@include('lead.partials.assign-modal', ['companies' => $companies ?? collect()])
@endcan
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    const isSuperAdmin = {{ auth()->user()->hasRole('superadmin') ? 'true' : 'false' }};
    const isTeleCaller = {{ auth()->user()->hasRole('tele-caller') ? 'true' : 'false' }};
    const currentUserCompanyId = '{{ auth()->user()->employee->company_id ?? '' }}';
    const currentUserName = @json(auth()->user()->name ?? '');
    $('.assign-lead-btn').on('click', function() {
        const leadId = $(this).data('lead-id');
        const leadCompanyId = $(this).data('lead-company-id') || '';
        const leadLocationId = $(this).data('lead-location-id') || '';
        const leadTeleCallerId = $(this).data('lead-tele-caller-id') || '';
        const leadSalesConsultantId = $(this).data('lead-sales-consultant-id') || '';
        const currentUserId = $(this).data('current-user-id') || '';
        if (!leadId) return;
        $('#assignLeadId').val(leadId);
        $('#assignModalError').addClass('d-none').text('');
        $('#assignModalNoCompany').addClass('d-none');
        $('#assignLocationId').html('<option value=\"\">Select Location first</option>').prop('disabled', true);
        $('#assignTeleCallerId').html('<option value=\"\">Select Tele-caller</option>').prop('disabled', true);
        $('#assignSalesConsultantId').html('<option value=\"\">Select Sales Consultant (optional)</option>').prop('disabled', true);
        $('#assignSubmitBtn').prop('disabled', true);
        $('#assignTeleCallerName').addClass('d-none').val('');
        $('#assignTeleCallerId').removeClass('d-none');

        // Prefill tele-caller and sales consultant for super admin or assigned tele-caller once dropdowns load
        window.assignLeadCurrentUserId = currentUserId;
        window.assignLeadPreload = (leadTeleCallerId || leadSalesConsultantId)
            ? { teleCallerId: leadTeleCallerId, salesConsultantId: leadSalesConsultantId }
            : null;

        if (leadCompanyId) {
            $('#assignCompanyId').val(leadCompanyId);
            $('#assignCompanyId').trigger('change');
            if (leadLocationId) {
                setTimeout(function() {
                    $('#assignLocationId').val(leadLocationId).trigger('change');
                }, 400);
            }
        } else if (!isSuperAdmin && currentUserCompanyId) {
            $('#assignCompanyId').val(currentUserCompanyId).trigger('change');
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

    $('#assignCompanyId').on('change', function() {
        $('#assignModalNoCompany').addClass('d-none');
        $('#assignModalError').addClass('d-none');
        const companyId = $(this).val();
        $('#assignLocationId').html('<option value=\"\">Select Location</option>').prop('disabled', !companyId);
        $('#assignTeleCallerId').html('<option value=\"\">Select Tele-caller</option>').prop('disabled', true);
        $('#assignSalesConsultantId').html('<option value=\"\">Select Sales Consultant (optional)</option>').prop('disabled', true);
        $('#assignSubmitBtn').prop('disabled', true);
        if (companyId) {
            $('#assignLocationId').html('<option value=\"\">Loading...</option>');
            $.get('{{ url("leads/ajax/locations") }}/' + companyId, function(data) {
                $('#assignLocationId').html('<option value=\"\">Select Location</option>').prop('disabled', false);
                data.forEach(function(loc) {
                    $('#assignLocationId').append('<option value=\"' + loc.id + '\">' + loc.name + (loc.state ? ', ' + loc.state : '') + '</option>');
                });
            }).fail(function() {
                $('#assignLocationId').html('<option value=\"\">Error loading locations</option>');
            });
        }
    });

    $('#assignLocationId').on('change', function() {
        const locationId = $(this).val();
        $('#assignTeleCallerId').html('<option value=\"\">Select Tele-caller</option>').prop('disabled', !locationId || (!isSuperAdmin && isTeleCaller));
        $('#assignSalesConsultantId').html('<option value=\"\">Select Sales Consultant (optional)</option>').prop('disabled', !locationId);
        $('#assignSubmitBtn').prop('disabled', true);
        if (locationId) {
            $('#assignTeleCallerId').html('<option value=\"\">Loading...</option>');
            $('#assignSalesConsultantId').html('<option value=\"\">Loading...</option>');
            $.get('{{ url("leads/ajax/assignable-users") }}/' + locationId, function(data) {
                $('#assignTeleCallerId').html('<option value=\"\">Select Tele-caller</option>').prop('disabled', !isSuperAdmin && isTeleCaller);
                (data.tele_callers || []).forEach(function(c) {
                    $('#assignTeleCallerId').append('<option value=\"' + c.id + '\">' + c.name + ' (' + (c.email || '') + ')</option>');
                });

                $('#assignSalesConsultantId').html('<option value=\"\">Select Sales Consultant (optional)</option>').prop('disabled', false);
                (data.sales_consultants || []).forEach(function(c) {
                    $('#assignSalesConsultantId').append('<option value=\"' + c.id + '\">' + c.name + ' (' + (c.email || '') + ')</option>');
                });

                // Prefill for super admin or assigned tele-caller when lead already has assignments
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
            }).fail(function() {
                $('#assignTeleCallerId').html('<option value=\"\">Error loading tele-callers</option>');
                $('#assignSalesConsultantId').html('<option value=\"\">Error loading sales consultants</option>');
            });
        }
    });

    $('#assignLocationId, #assignTeleCallerId, #assignSalesConsultantId, #assignCompanyId').on('change', function() {
        const hasCompany = $('#assignCompanyId').val();
        const hasLocation = $('#assignLocationId').val();
        const teleCallerId = $('#assignTeleCallerId').val();
        const salesConsultantId = $('#assignSalesConsultantId').val();

        let ok = false;
        if (isSuperAdmin) {
            ok = !!(hasCompany && hasLocation && teleCallerId);
        } else {
            ok = !!(hasCompany && hasLocation && salesConsultantId);
        }
        $('#assignSubmitBtn').prop('disabled', !ok);
    });

    $('#assignSubmitBtn').on('click', function() {
        const leadId = $('#assignLeadId').val();
        const companyId = $('#assignCompanyId').val();
        const locationId = $('#assignLocationId').val();
        const teleCallerId = $('#assignTeleCallerId').val();
        const salesConsultantId = $('#assignSalesConsultantId').val();
        if (!companyId) {
            $('#assignModalError').text('Please select a company.').removeClass('d-none');
            return;
        }
        if (!leadId || !locationId) return;
        const $btn = $(this);
        $btn.prop('disabled', true);
        $('#assignModalError').addClass('d-none');
        $.ajax({
            url: '{{ url("leads") }}/' + leadId + '/assign',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                company_id: companyId,
                location_id: locationId,
                tele_caller_id: teleCallerId,
                sales_consultant_id: salesConsultantId
            },
            success: function(res) {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('assignLeadModal')).hide();
                    window.location.reload();
                } else {
                    $('#assignModalError').text(res.message || 'Assign failed').removeClass('d-none');
                    $btn.prop('disabled', false);
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to assign. Please try again.';
                $('#assignModalError').text(msg).removeClass('d-none');
                $btn.prop('disabled', false);
            }
        });
    });
});
</script>
@endsection

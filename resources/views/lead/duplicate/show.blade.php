@extends('layouts/contentNavbarLayout')

@section('title', 'Duplicate Leads — ' . $user->name)

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    Duplicate Leads
</h4>

<!-- User Header Card -->
<div class="card mb-4">
    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar avatar-lg">
                <span class="avatar-initial rounded-circle bg-label-primary" style="font-size: 1.4rem;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </span>
            </div>
            <div>
                <h5 class="mb-1">{{ $user->name }}</h5>
                <div class="d-flex flex-wrap gap-1 align-items-center">
                    <small class="text-muted me-2">{{ $user->email }}</small>
                    @foreach($user->roles as $role)
                        <span class="badge bg-label-primary">{{ ucfirst(str_replace('-', ' ', $role->name)) }}</span>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <div class="text-center me-3">
                <div class="fw-bold fs-4 text-warning">{{ $groupedLeads->count() }}</div>
                <small class="text-muted">Duplicate Groups</small>
            </div>
            <div class="text-center me-3">
                <div class="fw-bold fs-4 text-danger">{{ $groupedLeads->flatten()->count() }}</div>
                <small class="text-muted">Total Duplicate Leads</small>
            </div>
            <a href="{{ route('leads.duplicates.index') }}" class="btn btn-secondary">
                <i class='bx bx-arrow-back me-1'></i> Back to List
            </a>
        </div>
    </div>
</div>

<!-- Duplicate Lead Groups -->
@if($groupedLeads->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class='bx bx-check-circle text-success' style="font-size: 3rem;"></i>
            <p class="mt-3 text-muted">No duplicate leads found for this user.</p>
        </div>
    </div>
@else
    <div class="accordion" id="duplicateAccordion">
        @foreach($groupedLeads as $groupKey => $leads)
        @php
            $firstLead     = $leads->first();
            $mobileNo      = $firstLead->mobile_no;
            $vehicleModel  = $firstLead->vehicleModel?->name ?? 'No Model';
            $accordionId   = 'group_' . md5($groupKey);
        @endphp
        <div class="accordion-item mb-3 border rounded shadow-sm">
            <h2 class="accordion-header" id="heading_{{ $accordionId }}">
                <button class="accordion-button collapsed fw-semibold" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapse_{{ $accordionId }}"
                        aria-expanded="false"
                        aria-controls="collapse_{{ $accordionId }}">
                    <i class='bx bx-phone me-2 text-primary'></i>
                    {{ $mobileNo }}
                    <span class="mx-2 text-muted">|</span>
                    <i class='bx bx-car me-2 text-secondary'></i>
                    {{ $vehicleModel }}
                    <span class="badge bg-danger ms-3">{{ $leads->count() }} leads</span>
                </button>
            </h2>
            <div id="collapse_{{ $accordionId }}"
                 class="accordion-collapse collapse"
                 aria-labelledby="heading_{{ $accordionId }}"
                 data-bs-parent="#duplicateAccordion">
                <div class="accordion-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Lead ID</th>
                                    <th>Customer Name</th>
                                    <th>Lead Type</th>
                                    <th>Lead Source</th>
                                    <th>Lead Status</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leads as $i => $lead)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <span class="badge bg-label-secondary">#{{ $lead->id }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $lead->first_name }} {{ $lead->last_name }}</span>
                                    </td>
                                    <td>
                                        @if($lead->leadType)
                                            <span class="badge bg-label-info">{{ $lead->leadType->name }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($lead->leadSource)
                                            {{ $lead->leadSource->campaign }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($lead->leadStatus)
                                            <span class="badge bg-label-danger">{{ ucfirst($lead->leadStatus->name) }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($lead->creator)
                                            {{ $lead->creator->name }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span title="{{ $lead->created_at }}">
                                            {{ $lead->created_at->format('d M Y, h:i A') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('leads.show', $lead->id) }}"
                                           class="btn btn-sm btn-primary"
                                           target="_blank">
                                            <i class='bx bx-show me-1'></i> View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif
@endsection

@section('page-style')
<style>
.accordion-button:not(.collapsed) {
    background-color: rgba(var(--bs-primary-rgb), 0.05);
    color: var(--bs-primary);
}
.accordion-item {
    overflow: hidden;
}
.table th {
    white-space: nowrap;
}
</style>
@endsection

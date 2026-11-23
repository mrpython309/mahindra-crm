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

@forelse($leads as $lead)
@php
    $tc = $lead->teleCallers->first();
    $sc = $lead->salesConsultants->first();
@endphp
<tr>
    @can('leads:assign')
    <td>
        <input
            type="checkbox"
            class="form-check-input bulk-lead-checkbox"
            value="{{ $lead->id }}"
            data-has-tele-caller="{{ $tc ? 1 : 0 }}"
        >
    </td>
    @endcan
    @if($showIdColumn)
    <td>{{ $lead->id }}</td>
    @endif
    <td>
        @if($lead->leadType)
            @php
                $badgeClass = match(strtolower($lead->leadType->name)) {
                    'hot'  => 'bg-label-danger',
                    'warm' => 'bg-label-warning',
                    'cold' => 'bg-label-info',
                    default => 'bg-label-secondary',
                };
            @endphp
            <span class="badge {{ $badgeClass }}">{{ $lead->leadType->name }}</span>
        @else
            -
        @endif
    </td>
    <td>{{ $lead->leadSource->campaign ?? '-' }}</td>
    <td>{{ $lead->created_at->format('d/m/Y') }}</td>
    <td>{{ $lead->first_name }} {{ $lead->last_name }}</td>
    <td>{{ $lead->mobile_no }}</td>
    @if($showCompanyColumn)
    <td>{{ $lead->company?->name ?? '-' }}</td>
    @endif
    <td>{{ $lead->city ?? '-' }}</td>
    <td>{{ $lead->next_follow_up ? \Carbon\Carbon::parse($lead->next_follow_up)->format('d/m/Y') : '-' }}</td>
    @if($showTeleCallerColumn)
    <td>
        {{ $tc ? $tc->name : 'Unassigned' }}
    </td>
    @endif
    <td>
        {{ $sc ? $sc->name : 'Not set' }}
    </td>
    <td>
        @include('lead.partials.record-status-badge', ['lead' => $lead])
    </td>
    <td>@include('lead.partials.table-row-actions', ['lead' => $lead])</td>
</tr>
@empty
<tr>
    <td colspan="{{ $leadTableColumnCount }}" class="text-center text-muted">No leads found</td>
</tr>
@endforelse

<div class="dropdown">
    <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
        <i class='bx bx-dots-vertical-rounded'></i>
    </button>
    <div class="dropdown-menu">
        @can('leads:view')
        <a class="dropdown-item" href="{{ route('leads.show', $lead->id) }}">
            <i class='bx bx-show me-1'></i> View
        </a>
        @endcan
        @can('leads:edit')
        <a class="dropdown-item" href="{{ route('leads.edit', $lead->id) }}">
            <i class='bx bx-edit me-1'></i> Edit
        </a>
        @endcan
        @can('leads:assign')
        <a class="dropdown-item assign-lead-btn" href="javascript:void(0)"
           data-lead-id="{{ $lead->id }}"
           data-lead-company-id="{{ $lead->company_id ?? '' }}"
           data-lead-location-id="{{ $lead->location_id ?? '' }}"
           data-lead-tele-caller-id="{{ $lead->primaryTeleCaller()?->id ?? '' }}"
           data-lead-sales-consultant-id="{{ $lead->primarySalesConsultant()?->id ?? '' }}"
           data-current-user-id="{{ auth()->id() }}">
            <i class='bx bx-user-check me-1'></i> Assign Lead
        </a>
        @endcan
        @can('leads:delete')
        <form action="{{ route('leads.destroy', $lead->id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="dropdown-item text-danger"
                    onclick="return confirm('Are you sure you want to delete this lead?')">
                <i class='bx bx-trash me-1'></i> Delete
            </button>
        </form>
        @endcan
    </div>
</div>

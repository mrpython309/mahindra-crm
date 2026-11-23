<div class="assignment-history">
    <h6 class="mb-3">
        <i class='bx bx-user-check me-2'></i>Assignment History
    </h6>
    @php
        $assignments = $lead->activities()->where('activity_type', 'lead_assigned')->orderBy('activity_date', 'desc')->with('creator')->get();
    @endphp
    @forelse($assignments as $activity)
        <div class="timeline-item mb-3">
            <div class="d-flex">
                <div class="me-3">
                    <div class="avatar avatar-sm">
                        <span class="avatar-initial rounded-circle bg-label-success">
                            <i class='bx bx-user-check'></i>
                        </span>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1">{{ $activity->title }}</h6>
                    <p class="mb-1 text-muted">{{ $activity->description }}</p>
                    <small class="text-muted">
                        {{ $activity->activity_date->format('d/m/Y H:i') }}
                        @if($activity->creator)
                            · by {{ $activity->creator->name }}
                        @else
                            · by System
                        @endif
                    </small>
                </div>
            </div>
        </div>
    @empty
        <p class="text-muted mb-0">No assignment history yet. Use "Assign Lead" to assign a tele-caller and sales consultant.</p>
    @endforelse
</div>

<div class="timeline">
    @forelse($lead->activities()->orderBy('activity_date', 'desc')->get() as $activity)
        <div class="timeline-item mb-3">
            <div class="d-flex">
                <div class="me-3">
                    <div class="avatar avatar-sm">
                        <span class="avatar-initial rounded-circle bg-label-primary">
                            <i class='bx bx-time'></i>
                        </span>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1">{{ $activity->title }}</h6>
                    <p class="mb-1">{{ $activity->description }}</p>
                    <small class="text-muted">
                        {{ $activity->activity_date->format('d/m/Y H:i') }} 
                        by {{ $activity->creator->name ?? 'System' }}
                    </small>
                </div>
            </div>
        </div>
    @empty
        <p class="text-muted">No activities recorded</p>
    @endforelse
</div>

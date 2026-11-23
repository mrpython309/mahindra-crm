@can('leads:notes:create')
<div class="mb-3">
    <form action="{{ route('leads.notes.store', $lead->id) }}" method="POST" id="addNoteForm">
        @csrf
        <div class="mb-2">
            <textarea name="note" class="form-control" rows="3" placeholder="Add a note..." required></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-sm" id="addNoteBtn">
            <span class="btn-text"><i class='bx bx-plus'></i> Add Note</span>
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
        </button>
    </form>
</div>
@endcan

@push('page-scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const noteForm = document.getElementById('addNoteForm');
    const addBtn = document.getElementById('addNoteBtn');

    if (noteForm) {
        noteForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Show loader
            const btnText = addBtn.querySelector('.btn-text');
            const spinner = addBtn.querySelector('.spinner-border');
            btnText.classList.add('d-none');
            spinner.classList.remove('d-none');
            addBtn.disabled = true;

            // Get form data
            const formData = new FormData(this);

            // Submit via AJAX
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reset form
                    noteForm.reset();

                    // Refresh only the notes list
                    fetch(window.location.href)
                        .then(response => response.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newContent = doc.querySelector('#notes-list-container');
                            if (newContent) {
                                document.querySelector('#notes-list-container').innerHTML = newContent.innerHTML;
                            }
                        });
                } else {
                    alert(data.message || 'An error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the note');
            })
            .finally(() => {
                // Hide loader
                btnText.classList.remove('d-none');
                spinner.classList.add('d-none');
                addBtn.disabled = false;
            });
        });
    }
});
</script>
@endpush

<div id="notes-list-container">
    <div class="notes-list">
    @forelse($lead->notes()->orderBy('is_pinned', 'desc')->orderBy('created_at', 'desc')->get() as $note)
        <div class="card mb-2 {{ $note->is_pinned ? 'border-warning' : '' }}">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div class="flex-grow-1">
                        @if($note->is_pinned)
                            <i class='bx bx-pin text-warning'></i>
                        @endif
                        <p class="mb-1">{{ $note->note }}</p>
                        <small class="text-muted">
                            {{ $note->created_at->format('d/m/Y H:i') }} by {{ $note->creator->name ?? '-' }}
                        </small>
                    </div>
                    <div class="d-flex gap-1">
                        @can('leads:notes:create')
                        <form action="{{ route('leads.notes.toggle-pin', $note->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-warning" title="{{ $note->is_pinned ? 'Unpin' : 'Pin' }}">
                                <i class='bx bx-pin'></i>
                            </button>
                        </form>
                        @endcan
                        @can('leads:notes:delete')
                        <form action="{{ route('leads.notes.destroy', $note->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this note?')">
                                <i class='bx bx-trash'></i>
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    @empty
        <p class="text-muted">No notes added</p>
    @endforelse
    </div>
</div>

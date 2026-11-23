@can('leads:tags:create')
<div class="mb-3">
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTagModal">
        <i class='bx bx-plus'></i> Add Tag
    </button>
</div>
@endcan

<div id="tags-list-container">
    <div class="d-flex flex-wrap gap-2">
    @forelse($lead->tags as $tag)
        <div class="d-inline-flex align-items-center">
            <span class="badge" style="background-color: {{ $tag->color }}; font-size: 0.9rem; padding: 0.5rem 0.75rem;">
                {{ $tag->tag_name }}
                @can('leads:tags:delete')
                <form action="{{ route('leads.tags.destroy', $tag->id) }}" method="POST" class="d-inline ms-2">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-link p-0 text-white" style="font-size: 1rem; line-height: 1;" onclick="return confirm('Remove this tag?')">
                        ×
                    </button>
                </form>
                @endcan
            </span>
        </div>
    @empty
        <p class="text-muted">No tags added</p>
    @endforelse
    </div>
</div>

@can('leads:tags:create')
<!-- Add Tag Modal -->
<div class="modal fade" id="addTagModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="tagForm" action="{{ route('leads.tags.store', $lead->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Tag</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tag Name <span class="text-danger">*</span></label>
                        <input type="text" name="tag_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <input type="color" name="color" class="form-control" value="#6c757d">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="addTagBtn">
                        <span class="btn-text">Add Tag</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@push('page-scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tagForm = document.getElementById('tagForm');
    const addBtn = document.getElementById('addTagBtn');
    const modal = new bootstrap.Modal(document.getElementById('addTagModal'));

    if (tagForm) {
        tagForm.addEventListener('submit', function(e) {
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
                    // Close modal
                    modal.hide();

                    // Reset form
                    tagForm.reset();

                    // Refresh only the tags list
                    fetch(window.location.href)
                        .then(response => response.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newContent = doc.querySelector('#tags-list-container');
                            if (newContent) {
                                document.querySelector('#tags-list-container').innerHTML = newContent.innerHTML;
                            }
                        });
                } else {
                    alert(data.message || 'An error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the tag');
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

@can('leads:attachments:create')
<div class="mb-3">
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadAttachmentModal">
        <i class='bx bx-upload'></i> Upload Attachment
    </button>
</div>
@endcan

<div id="attachments-list-container">
    <div class="table-responsive">
        <table class="table">
        <thead>
            <tr>
                <th>SL</th>
                <th>Title</th>
                <th>Type</th>
                <th>File Size</th>
                <th>Uploaded By</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lead->attachments as $attachment)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $attachment->title }}</td>
                    <td>
                        <i class='bx bx-file text-primary' style="font-size: 1.5rem;"></i>
                        {{ strtoupper(pathinfo($attachment->file_name, PATHINFO_EXTENSION)) }}
                    </td>
                    <td>{{ number_format($attachment->file_size / 1024, 2) }} KB</td>
                    <td>{{ $attachment->uploader->name ?? '-' }}</td>
                    <td>{{ $attachment->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('leads.attachments.download', $attachment->id) }}" class="btn btn-sm btn-info" title="Download">
                            <i class='bx bx-download'></i>
                        </a>
                        @can('leads:attachments:delete')
                        <form action="{{ route('leads.attachments.destroy', $attachment->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this attachment?')" title="Delete">
                                <i class='bx bx-trash'></i>
                            </button>
                        </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">No attachments found</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@can('leads:attachments:create')
<!-- Upload Modal -->
<div class="modal fade" id="uploadAttachmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="attachmentForm" action="{{ route('leads.attachments.store', $lead->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Upload Attachment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <select name="title" class="form-select" required>
                            <option value="">Select Title</option>
                            <option value="Aadhar">Aadhar</option>
                            <option value="PAN">PAN</option>
                            <option value="ID Proof">ID Proof</option>
                            <option value="Address Proof">Address Proof</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">File <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" required accept=".jpg,.jpeg,.png,.pdf">
                        <small class="text-muted">Allowed types: JPG, JPEG, PNG, PDF. Max file size: 2MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="uploadAttachmentBtn">
                        <span class="btn-text">Upload</span>
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
    const attachmentForm = document.getElementById('attachmentForm');
    const uploadBtn = document.getElementById('uploadAttachmentBtn');
    const modal = new bootstrap.Modal(document.getElementById('uploadAttachmentModal'));

    if (attachmentForm) {
        attachmentForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Show loader
            const btnText = uploadBtn.querySelector('.btn-text');
            const spinner = uploadBtn.querySelector('.spinner-border');
            btnText.classList.add('d-none');
            spinner.classList.remove('d-none');
            uploadBtn.disabled = true;

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
                    attachmentForm.reset();

                    // Refresh only the attachments list
                    fetch(window.location.href)
                        .then(response => response.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newContent = doc.querySelector('#attachments-list-container');
                            if (newContent) {
                                document.querySelector('#attachments-list-container').innerHTML = newContent.innerHTML;
                            }
                        });
                } else {
                    alert(data.message || 'An error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while uploading the attachment');
            })
            .finally(() => {
                // Hide loader
                btnText.classList.remove('d-none');
                spinner.classList.add('d-none');
                uploadBtn.disabled = false;
            });
        });
    }
});
</script>
@endpush

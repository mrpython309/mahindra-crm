@can('leads:reminders:create')
<div class="mb-3">
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addReminderModal">
        <i class='bx bx-plus'></i> Add Reminder
    </button>
</div>
@endcan

<div id="reminders-list-container">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Reminder Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lead->reminders as $reminder)
                    <tr>
                        <td>{{ $reminder->title }}</td>
                        <td>{{ Str::limit($reminder->description, 50) }}</td>
                        <td>{{ $reminder->reminder_date->format('d/m/Y H:i') }}</td>
                        <td>
                            <span class="badge bg-{{ $reminder->is_sent ? 'success' : 'warning' }}">
                                {{ $reminder->is_sent ? 'Sent' : 'Pending' }}
                            </span>
                        </td>
                        <td>
                            @can('leads:reminders:delete')
                            <form action="{{ route('leads.reminders.destroy', $reminder->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this reminder?')">
                                    <i class='bx bx-trash'></i>
                                </button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No reminders set</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@can('leads:reminders:create')
<!-- Add Reminder Modal -->
<div class="modal fade" id="addReminderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="reminderForm" action="{{ route('leads.reminders.store', $lead->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Reminder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reminder Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="reminder_date" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveReminderBtn">
                        <span class="btn-text">Save Reminder</span>
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
    const reminderForm = document.getElementById('reminderForm');
    const saveBtn = document.getElementById('saveReminderBtn');
    const modal = new bootstrap.Modal(document.getElementById('addReminderModal'));

    if (reminderForm) {
        reminderForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Show loader
            const btnText = saveBtn.querySelector('.btn-text');
            const spinner = saveBtn.querySelector('.spinner-border');
            btnText.classList.add('d-none');
            spinner.classList.remove('d-none');
            saveBtn.disabled = true;

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
                    reminderForm.reset();

                    // Refresh only the reminders list
                    fetch(window.location.href)
                        .then(response => response.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newContent = doc.querySelector('#reminders-list-container');
                            if (newContent) {
                                document.querySelector('#reminders-list-container').innerHTML = newContent.innerHTML;
                            }
                        });
                } else {
                    alert(data.message || 'An error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving the reminder');
            })
            .finally(() => {
                // Hide loader
                btnText.classList.remove('d-none');
                spinner.classList.add('d-none');
                saveBtn.disabled = false;
            });
        });
    }
});
</script>
@endpush

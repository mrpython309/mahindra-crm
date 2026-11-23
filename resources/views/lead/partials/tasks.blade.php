@can('leads:tasks:create')
<div class="mb-3">
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTaskModal">
        <i class='bx bx-plus'></i> Add Task
    </button>
</div>
@endcan

<div id="tasks-list-container">
    <div class="table-responsive">
        <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Due Date</th>
                <th>Priority</th>
                <th>Assigned To</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lead->tasks as $task)
                <tr class="task-priority-{{ $task->priority }}">
                    <td>{{ $task->title }}</td>
                    <td>{{ $task->due_date ? $task->due_date->format('d/m/Y') : '-' }}</td>
                    <td>
                        <span class="badge bg-{{ $task->priority == 'high' ? 'danger' : ($task->priority == 'medium' ? 'warning' : 'info') }}">
                            {{ ucfirst($task->priority) }}
                        </span>
                    </td>
                    <td>{{ $task->assignedUser->name ?? '-' }}</td>
                    <td>
                        <span class="badge bg-{{ $task->status == 'completed' ? 'success' : ($task->status == 'in_progress' ? 'warning' : 'secondary') }}">
                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                        </span>
                    </td>
                    <td>
                        @can('leads:tasks:delete')
                        <form action="{{ route('leads.tasks.destroy', $task->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this task?')">
                                <i class='bx bx-trash'></i>
                            </button>
                        </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">No tasks created</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@can('leads:tasks:create')
<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="taskForm" action="{{ route('leads.tasks.store', $lead->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Task</h5>
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
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Priority <span class="text-danger">*</span></label>
                        <select name="priority" class="form-select" required>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="pending" selected>Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveTaskBtn">
                        <span class="btn-text">Save Task</span>
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
    const taskForm = document.getElementById('taskForm');
    const saveBtn = document.getElementById('saveTaskBtn');
    const modal = new bootstrap.Modal(document.getElementById('addTaskModal'));

    if (taskForm) {
        taskForm.addEventListener('submit', function(e) {
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
                    taskForm.reset();

                    // Refresh only the tasks list
                    fetch(window.location.href)
                        .then(response => response.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newContent = doc.querySelector('#tasks-list-container');
                            if (newContent) {
                                document.querySelector('#tasks-list-container').innerHTML = newContent.innerHTML;
                            }
                        });
                } else {
                    alert(data.message || 'An error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving the task');
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

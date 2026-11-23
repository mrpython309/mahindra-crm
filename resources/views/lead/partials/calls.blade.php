@can('leads:calls:create')
<div class="mb-3">
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCallModal">
        <i class='bx bx-plus'></i> Add Follow-up
    </button>
</div>
@endcan

<div id="calls-list-container">
    <div class="table-responsive">
        <table class="table">
        <thead>
            <tr>
                <th>SL</th>
                <th>Follow-up Date</th>
                <th>Number</th>
                <th>Type</th>
                <th>Lead Response</th>
                <th>Test Drive Scheduled</th>
                <th>Likely Purchase</th>
                <th>Next Follow-up</th>
                <th>Logged By</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lead->calls as $call)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $call->call_date->format('d/m/Y') }}</td>
                    <td>{{ $call->number }}</td>
                    <td>
                        <span class="badge bg-{{ $call->type == 'incoming' ? 'success' : 'info' }}">
                            {{ ucfirst($call->type) }}
                        </span>
                    </td>
                    <td>
                        @if($call->responseMaster)
                            {{ $call->responseMaster->response }}
                            @if($call->responseMaster->leadStatus)
                                ({{ ucfirst($call->responseMaster->leadStatus->name) }})
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        {{ $call->test_drive_at ? $call->test_drive_at->format('d/m/Y H:i') : '-' }}
                    </td>
                    <td>
                        @if($call->likelyLeadType)
                            @php
                                $ltName = strtolower($call->likelyLeadType->name);
                                $ltBadge = match($ltName) {
                                    'hot' => 'badge bg-label-danger',
                                    'warm' => 'badge bg-label-warning',
                                    'cold' => 'badge bg-label-info',
                                    default => 'badge bg-label-secondary'
                                };
                            @endphp
                            <span class="{{ $ltBadge }}">{{ $call->likelyLeadType->name }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $call->next_follow_up_date ? $call->next_follow_up_date->format('d/m/Y') : '-' }}</td>
                    <td>{{ $call->creator->name ?? '-' }}</td>
                    <td>
                        @can('leads:calls:delete')
                        <form action="{{ route('leads.calls.destroy', $call->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this follow-up?')">
                                <i class='bx bx-trash'></i>
                            </button>
                        </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center text-muted">No follow-ups added</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@can('leads:calls:create')
<!-- Add Follow-up Modal -->
<div class="modal fade" id="addCallModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="callForm" action="{{ route('leads.calls.store', $lead->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Follow-up</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Call Date <span class="text-danger">*</span></label>
                            <input type="date" name="call_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Number <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="number"
                                class="form-control"
                                value="{{ old('number', $lead->mobile_no) }}"
                                required
                            >
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="incoming">Incoming</option>
                                <option value="outgoing">Outgoing</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Lead Response</label>
                            <select name="lead_response" id="call_lead_response" class="form-select">
                                <option value="">Select response</option>
                                @php
                                    $statusSources = \App\Models\StatusSource::with('leadStatus')->get();
                                @endphp
                                @foreach($statusSources as $response)
                                    <option value="{{ $response->id }}" data-response-name="{{ strtolower(trim($response->response)) }}">
                                        {{ $response->response }} @if($response->leadStatus) ({{ $response->leadStatus->name }}) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6" id="call_test_drive_at_wrap" style="display: none;">
                            <label class="form-label">Test Drive Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="test_drive_at" id="call_test_drive_at" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Likely Purchase</label>
                            <select name="likely_purchase" class="form-select">
                                <option value="">Select likely purchase</option>
                                @php
                                    $leadTypes = \App\Models\LeadType::whereNotNull('likely_purchase')->get();
                                @endphp
                                @foreach($leadTypes as $type)
                                    <option value="{{ $type->likely_purchase }}">
                                        @if($type->likely_purchase == 'under_15_days')
                                            Under 15 Days
                                        @elseif($type->likely_purchase == '15_to_45_days')
                                            15 to 45 Days
                                        @elseif($type->likely_purchase == 'above_45_days')
                                            Above 45 Days
                                        @elseif($type->likely_purchase == 'invalid_enquiry_closed')
                                            Invalid Enquiry (Closed)
                                        @else
                                            {{ $type->likely_purchase }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Next Follow-up Date</label>
                            <input type="date" name="next_follow_up_date" class="form-control" min="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveCallBtn">
                        <span class="btn-text">Save Follow-up</span>
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
    const callForm = document.getElementById('callForm');
    const saveBtn = document.getElementById('saveCallBtn');
    const modal = new bootstrap.Modal(document.getElementById('addCallModal'));
    const leadResponseSelect = document.getElementById('call_lead_response');
    const testDriveWrap = document.getElementById('call_test_drive_at_wrap');
    const testDriveInput = document.getElementById('call_test_drive_at');

    function currentDateTimeLocalValue() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    function toggleCallTestDriveField() {
        if (!leadResponseSelect || !testDriveWrap || !testDriveInput) return;

        const selected = leadResponseSelect.options[leadResponseSelect.selectedIndex];
        const responseName = ((selected && selected.dataset ? selected.dataset.responseName : '') || '')
            .toString()
            .toLowerCase()
            .replace(/[\s_-]+/g, ' ')
            .trim();
        const isTestDriveScheduled = responseName.includes('test drive') && responseName.includes('scheduled');

        if (isTestDriveScheduled) {
            testDriveWrap.style.display = '';
            testDriveInput.required = true;
            testDriveInput.min = currentDateTimeLocalValue();
        } else {
            testDriveWrap.style.display = 'none';
            testDriveInput.required = false;
            testDriveInput.value = '';
        }
    }

    if (leadResponseSelect) {
        leadResponseSelect.addEventListener('change', toggleCallTestDriveField);
        toggleCallTestDriveField();
    }

    if (callForm) {
        callForm.addEventListener('submit', function(e) {
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
                    callForm.reset();

                    // Refresh only the calls list
                    fetch(window.location.href)
                        .then(response => response.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newContent = doc.querySelector('#calls-list-container');
                            if (newContent) {
                                document.querySelector('#calls-list-container').innerHTML = newContent.innerHTML;
                            }
                        });
                } else {
                    alert(data.message || 'An error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while logging the call');
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

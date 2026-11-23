{{-- Assign Lead Modal - Tele-caller and Sales Consultant (requires $companies to be passed) --}}
<div class="modal fade" id="assignLeadModal" tabindex="-1" aria-labelledby="assignLeadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignLeadModalLabel">
                    <i class='bx bx-user-check me-2'></i>Assign Lead
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="assignModalError" class="alert alert-danger d-none" role="alert"></div>
                <div id="assignModalModeHint" class="alert alert-info d-none" role="alert"></div>
                <div id="assignModalNoCompany" class="alert alert-warning d-none" role="alert">
                    <i class='bx bx-info-circle me-2'></i>This lead has no company selected. Please select a company below to continue.
                </div>
                <input type="hidden" id="assignLeadId" value="">
                <input type="hidden" id="assignLeadIds" value="">
                <input type="hidden" id="assignActionMode" value="single">
                <div class="mb-3" id="assignCompanyWrap">
                    <label class="form-label">Company <span class="text-danger">*</span></label>
                    <select id="assignCompanyId" class="form-select" required>
                        <option value="">Select Company</option>
                        @foreach(collect($companies ?? []) as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3" id="assignLocationWrap">
                    <label class="form-label">Location <span class="text-danger">*</span></label>
                    <select id="assignLocationId" class="form-select" required disabled>
                        <option value="">Select Location first</option>
                    </select>
                </div>
                <div class="mb-3" id="assignTeleCallerWrap">
                    <label class="form-label">Tele-caller <span class="text-danger">*</span></label>
                    <input type="text" id="assignTeleCallerName" class="form-control d-none" readonly>
                    <select id="assignTeleCallerId" class="form-select" required disabled>
                        <option value="">Select Tele-caller</option>
                    </select>
                    <small class="text-muted d-block mt-1">
                        Every lead must have at least one tele-caller.
                    </small>
                </div>
                <div class="mb-3" id="assignSalesConsultantWrap">
                    <label class="form-label">Sales Consultant</label>
                    <select id="assignSalesConsultantId" class="form-select" disabled>
                        <option value="">Select Sales Consultant (optional)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="assignSubmitBtn" class="btn btn-primary" disabled>
                    <i class='bx bx-check me-1'></i> Assign
                </button>
            </div>
        </div>
    </div>
</div>

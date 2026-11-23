@extends('layouts/contentNavbarLayout')

@section('title', 'Create Employee')

@section('page-style')
<style>
    .form-container {
        background: #f8f9fa;
        border-radius: 0.5rem;
        padding: 2rem;
        border: 1px solid #e3e6f0;
        margin-bottom: 1.5rem;
    }
    /* .form-label-required::after {
        content: " *";
        color: #dc3545;
    } */
    .form-section-title {
        color: #495057;
        font-weight: 600;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e9ecef;
    }


</style>
@endsection

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    <span class="text-muted fw-light">Create Employee</span>
</h4>

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">New Employee</h5>
        <a href="{{route('employee.index')}}" class="btn btn-secondary">Back to list</a>
    </div>

    <div class="card-body">
        <div class="form-container">
            <form action="{{route('employee.store')}}" method="POST">
                @csrf

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- BASIC DETAILS --}}
                <div class="form-section-title">Basic Details</div>
                <div class="row g-3">
                    <div class="col-md-4 mb-3">
                        <label class="form-label form-label-required">
                            Emp Code <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            name="emp_code"
                            class="form-control @error('emp_code') is-invalid @enderror"
                            value="{{ old('emp_code') }}"
                            placeholder="Enter Emp Code"

                        >
                        @error('emp_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label form-label-required">
                            MILE ID <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            name="mile_id"
                            class="form-control @error('mile_id') is-invalid @enderror"
                            value="{{ old('mile_id') }}"
                            placeholder="Enter MILE ID"

                        >
                        @error('mile_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label form-label-required">
                            Employee Name <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            name="employee_name"
                            class="form-control @error('employee_name') is-invalid @enderror"
                            value="{{ old('employee_name') }}"
                            placeholder="Enter Employee Name"

                        >
                        @error('employee_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label form-label-required">Date of Joining <span class="text-danger">*</span></label>
                        <input
                            type="date"
                            name="date_of_joining"
                            class="form-control @error('date_of_joining') is-invalid @enderror"
                            value="{{ old('date_of_joining',date('Y-m-d')) }}"
                            max="{{ date('Y-m-d') }}"

                        >
                        @error('date_of_joining')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>


                    <div class="col-md-4 mb-3">
                        <label class="form-label form-label-required">
                            Mobile Number <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            name="mobile_number"
                            class="form-control @error('mobile_number') is-invalid @enderror"
                            value="{{ old('mobile_number') }}"
                            placeholder="Enter Mobile Number"

                        >
                        @error('mobile_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label form-label-required">
                            Email ID <span class="text-danger">*</span>
                        </label>
                        <input
                            type="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email') }}"
                            placeholder="Enter Email"
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>



                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            Designation <span class="text-danger">*</span>
                        </label>
                        <select
                            name="designation_id" id="designation_id"
                            class="form-select @error('designation_id') is-invalid @enderror"

                        >
                            <option value="">Select Designation</option>
                            @foreach($designations as $designation)
                                <option value="{{ $designation->id }}"
                                    {{ old('designation_id') == $designation->id ? 'selected' : '' }}>
                                    {{ $designation->designation_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('designation_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                {{-- USER DETAILS --}}
                <div class="form-section-title mt-4">User Account</div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label form-label-required">Is User <span class="text-danger">*</span></label>
                        <select class="form-select" name="is_user" id="is_user">
                            <option value="no" {{ old('is_user', 'no') == 'no' ? 'selected' : '' }}>No</option>
                            <option value="yes" {{ old('is_user') == 'yes' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>

                    <div class="col-md-4 d-none" id="password_div">
                        <label class="form-label form-label-required">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password" id="emp_password" class="form-control @error('password') is-invalid @enderror">
                            <button class="btn btn-outline-secondary" type="button" id="toggleEmpPassword">
                                <i class="bx bx-hide" id="empPasswordIcon"></i>
                            </button>
                        </div>
                        <div class="form-text">Password must be at least 8 characters</div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3 d-none" id="role">
                        <label class="form-label form-label-required">
                            Role <span class="text-danger">*</span>
                        </label>
                        <select
                            name="role_id" id="role_id"
                            class="form-select @error('role_id') is-invalid @enderror"

                        >
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}"
                                    {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('role_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            Employee Status <span class="text-danger">*</span>
                        </label>
                        <select
                            name="employee_status" id="employee_status"
                            class="form-select @error('employee_status') is-invalid @enderror">
                            <option value="active" {{ old('employee_status') == 'active' ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="inactive" {{ old('employee_status') == 'inactive' ? 'selected' : '' }}>
                                Inactive
                            </option>
                        </select>
                        @error('employee_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                {{-- ORGANIZATION DETAILS --}}
                <div class="form-section-title mt-4 " id="organization_title">Organization Details </div>
                <div class="row g-3" id="organization">

                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            Company <span class="text-danger">*</span>
                        </label>
                        <select
                            name="company_id" id="company_id"
                            class="form-select @error('company_id') is-invalid @enderror">
                            <option value="">Select Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}"
                                    {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('company_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="border rounded-3 mb-3" id="location-box" style="display:none;">
                        <div class="bg-light p-3 rounded-top d-flex justify-content-between align-items-center">
                            <span class="fw-semibold text-capitalize" id="company-title">

                            </span>

                            <!-- <button
                                type="button"
                                class="btn btn-sm btn-outline-secondary select-group"
                                data-target="location-group"
                            >
                                Select All
                            </button> -->
                        </div>

                        <div class="row px-3 py-3" id="location-group">
                            {{-- AJAX locations will come here --}}
                        </div>
                    </div>

                    <div class="border rounded-3 mb-3">
                        <div class="bg-light p-3 rounded-top d-flex justify-content-between align-items-center">
                            <span class="fw-semibold text-capitalize">
                                Segment <span class="text-danger">*</span>
                            </span>
                        </div>
                        <div class="row px-3 py-3" id="segment-group">
                            @foreach($segments as $segment)
                                <div class="col-md-3 col-sm-6 mb-2">
                                    <div class="form-check">
                                        <input
                                            type="checkbox"
                                            class="form-check-input segment-checkbox"
                                            id="segment_{{ $segment->id }}"
                                            name="segment_id[]"
                                            value="{{ $segment->id }}"
                                            {{ in_array($segment->id, old('segment_id', [])) ? 'checked' : '' }}
                                        >
                                        <label class="form-check-label text-muted" for="segment_{{ $segment->id }}">
                                            {{ $segment->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('segment_id')
                            <div class="text-danger small px-3 pb-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="border rounded-3 mb-3" id="subsegment-box" style="display:none;">
                        <div class="bg-light p-3 rounded-top d-flex justify-content-between align-items-center">
                            <span class="fw-semibold text-capitalize" id="segment-title">
                                Sub Segment
                            </span>

                            <!-- <button
                                type="button"
                                class="btn btn-sm btn-outline-secondary select-group"
                                data-target="subsegment-group"
                            >
                                Select All
                            </button> -->
                        </div>

                        <div class="row px-3 py-3" id="subsegment-group"></div>
                    </div>

                    <div class="border rounded-3 mb-3">
                        <div class="bg-light p-3 rounded-top">
                            <span class="fw-semibold text-capitalize">Reporting To</span>
                            <p class="text-muted small mb-0 mt-1">Up to three managers. Each person can only be selected in one slot. All slots are optional.</p>
                        </div>

                        <div class="px-3 py-3" id="reporting-placeholder">
                            <p class="text-muted mb-0 small">Select a company above to load report-to persons.</p>
                        </div>

                        <div class="px-3 pb-3" id="reporting-selects-wrap" style="display:none;">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="report_to_person_1" class="form-label">Report Person 1</label>
                                    <select class="form-select" name="report_to_person_1" id="report_to_person_1">
                                        <option value="">— Optional —</option>
                                    </select>
                                    @error('report_to_person_1')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="report_to_person_2" class="form-label">Report Person 2</label>
                                    <select class="form-select" name="report_to_person_2" id="report_to_person_2">
                                        <option value="">— Optional —</option>
                                    </select>
                                    @error('report_to_person_2')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="report_to_person_3" class="form-label">Report Person 3</label>
                                    <select class="form-select" name="report_to_person_3" id="report_to_person_3">
                                        <option value="">— Optional —</option>
                                    </select>
                                    @error('report_to_person_3')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="border rounded-3 mb-3">
                        <div class="bg-light p-3 rounded-top d-flex justify-content-between align-items-center">
                            <span class="fw-semibold text-capitalize">Department</span>

                            <!-- <button
                                type="button"
                                class="btn btn-sm btn-outline-secondary select-group"
                                data-target="department-group"
                            >
                                Select All
                            </button> -->
                        </div>

                        <div class="row px-3 py-3" id="department-group">
                            @foreach ($departments as $department)
                                <div class="col-md-3 col-sm-6 mb-2">
                                    <div class="form-check">
                                        <input
                                            type="checkbox"
                                            class="form-check-input department-checkbox"
                                            id="department_{{ $department->id }}"
                                            name="department_id[]"
                                            value="{{ $department->id }}"
                                            {{ in_array($department->id, old('department_id', [])) ? 'checked' : '' }}
                                        >
                                        <label
                                            class="form-check-label text-muted"
                                            for="department_{{ $department->id }}"
                                        >
                                            {{ $department->department_name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- ACTIONS --}}
                <div class="mt-4 text-end">
                    <button class="btn btn-primary">Save Employee</button>
                    <a href="{{route('employee.index')}}" class="btn btn-outline-secondary">Cancel</a>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>

    document.getElementById('is_user').addEventListener('change', function () {
        document.getElementById('password_div').classList.toggle('d-none', this.value !== 'yes');
        document.getElementById('role').classList.toggle('d-none', this.value !== 'yes');
    });

    document.addEventListener('DOMContentLoaded', function () {
        const isUserSelect = document.getElementById('is_user');
        if (isUserSelect && isUserSelect.value === 'yes') {
            document.getElementById('password_div').classList.remove('d-none');
            document.getElementById('role').classList.remove('d-none');
        }

    });

    const toggleEmpPassword = document.getElementById('toggleEmpPassword');
    const empPasswordInput = document.getElementById('emp_password');
    const empPasswordIcon = document.getElementById('empPasswordIcon');

    if (toggleEmpPassword) {
        toggleEmpPassword.addEventListener('click', function () {
            const isPassword = empPasswordInput.type === 'password';
            empPasswordInput.type = isPassword ? 'text' : 'password';
            empPasswordIcon.className = isPassword ? 'bx bx-show' : 'bx bx-hide';
        });
    }

</script>

<script>
// Wait for jQuery to be available (Vite/asset pipeline may load it asynchronously)
(function checkJQueryForEmployeeCreate() {
    if (typeof jQuery !== 'undefined') {
        initializeEmployeeCreatePage();
    } else {
        setTimeout(checkJQueryForEmployeeCreate, 50);
    }
})();

function initializeEmployeeCreatePage() {
    @php
        $rtLegacy = old('reporting_to', []);
        $rpt1 = old('report_to_person_1', $rtLegacy[0] ?? '');
        $rpt2 = old('report_to_person_2', $rtLegacy[1] ?? '');
        $rpt3 = old('report_to_person_3', $rtLegacy[2] ?? '');
        $includeReportIds = collect([$rpt1, $rpt2, $rpt3])->filter()->implode(',');
    @endphp

    let byCompanyUrl = "{{ route('employee.byLocations', ':companyId') }}";
    let employeesByCompanyUrl = "{{ route('employee.employeesByCompany', ['company' => ':companyId']) }}";
    let selectedLocations = @json(old('dealer_location_id', []));
    const pageInitialReportSlots = [@json((string) $rpt1), @json((string) $rpt2), @json((string) $rpt3)];
    const includeReportEmployeeIds = @json($includeReportIds);

    let reportingEmployeesList = [];
    let reportingSelectsHydrated = false;
    let usePageInitialReportSlots = true;

    function escapeReportLabel(text) {
        const div = document.createElement('div');
        div.textContent = text == null ? '' : String(text);
        return div.innerHTML;
    }

    function syncReportToDropdowns() {
        const current = [];
        for (let i = 0; i < 3; i++) {
            const $el = $(`#report_to_person_${i + 1}`);
            let v = $el.val() || '';
            if (usePageInitialReportSlots && !reportingSelectsHydrated && pageInitialReportSlots[i]) {
                v = String(pageInitialReportSlots[i]);
            }
            current.push(v);
        }
        reportingSelectsHydrated = true;
        for (let i = 0; i < 3; i++) {
            const taken = current.filter((val, j) => j !== i && val);
            let opts = '<option value="">— Optional —</option>';
            reportingEmployeesList.forEach(function (m) {
                const idStr = String(m.id);
                if (taken.includes(idStr) && idStr !== current[i]) {
                    return;
                }
                const desig = (m.designation && m.designation.designation_name) ? m.designation.designation_name : '';
                const label = desig ? `${m.employee_name} (${desig})` : (m.employee_name || '');
                const sel = idStr === current[i] ? ' selected' : '';
                opts += `<option value="${idStr}"${sel}>${escapeReportLabel(label)}</option>`;
            });
            $(`#report_to_person_${i + 1}`).html(opts);
        }
    }

    function resetReportingSelectPlaceholders() {
        for (let i = 1; i <= 3; i++) {
            $(`#report_to_person_${i}`).html('<option value="">— Optional —</option>');
        }
    }

    function loadLocations(companyId, companyName) {

        if (!companyId) {
            $('#location-group').html('');
            $('#location-box').hide();
            return;
        }

        // Set company name as title
        $('#company-title').text(companyName);

        let url = byCompanyUrl.replace(':companyId', companyId);

        $.ajax({
            url: url,
            type: 'GET',
            success: function (data) {

                let html = '';

                data.forEach(location => {

                    let checked = selectedLocations.includes(location.id.toString())
                        ? 'checked'
                        : '';

                    html += `
                        <div class="col-md-3 col-sm-6 mb-2">
                            <div class="form-check">
                                <input
                                    type="checkbox"
                                    class="form-check-input location-checkbox"
                                    name="dealer_location_id[]"
                                    value="${location.id}"
                                    id="location_${location.id}"
                                    ${checked}
                                >
                                <label
                                    class="form-check-label text-muted"
                                    for="location_${location.id}"
                                >
                                    ${location.name}
                                </label>
                            </div>
                        </div>
                    `;
                });

                $('#location-group').html(html);
                $('#location-box').show();
            }
        });
    }

    // Company change
    $('#company_id').on('change', function () {

        let companyId   = $(this).val();
        let companyName = $('#company_id option:selected').text();

        usePageInitialReportSlots = false;
        reportingSelectsHydrated = false;
        resetReportingSelectPlaceholders();

        loadLocations(companyId, companyName);
        loadReportingEmployees(companyId);
    });

    $(document).on('change', '#report_to_person_1, #report_to_person_2, #report_to_person_3', function () {
        syncReportToDropdowns();
    });

    // Load locations on validation error (old company_id)
    @if(old('company_id'))
        loadLocations(
            {{ old('company_id') }},
            $('#company_id option:selected').text()
        );
        loadReportingEmployees({{ old('company_id') }});
    @endif

    function loadReportingEmployees(companyId) {
        if (!companyId) {
            $('#reporting-placeholder').show().html('<p class="text-muted mb-0 small">Select a company above to load report-to persons.</p>');
            $('#reporting-selects-wrap').hide();
            reportingEmployeesList = [];
            reportingSelectsHydrated = false;
            resetReportingSelectPlaceholders();
            return;
        }

        let url = employeesByCompanyUrl.replace(':companyId', companyId);
        if (includeReportEmployeeIds) {
            url += (url.indexOf('?') >= 0 ? '&' : '?') + 'include_report_employee_ids=' + encodeURIComponent(includeReportEmployeeIds);
        }

        $('#reporting-placeholder').show().html('<p class="text-muted mb-0 small"><span class="spinner-border spinner-border-sm" role="status"></span> Loading...</p>');
        $('#reporting-selects-wrap').hide();

        $.ajax({
            url: url,
            type: 'GET',
            success: function (data) {
                reportingEmployeesList = data || [];
                if (reportingEmployeesList.length === 0) {
                    $('#reporting-placeholder').show().html('<p class="text-muted mb-0 small">No employees found in this company.</p>');
                    $('#reporting-selects-wrap').hide();
                    return;
                }
                reportingSelectsHydrated = false;
                $('#reporting-selects-wrap').show();
                $('#reporting-placeholder').hide();
                syncReportToDropdowns();
            }
        });
    }

    let bySegmentUrl = "{{ route('employee.bySegment', ':segmentId') }}";
    let selectedSubsegments = @json(old('subsegment_id', []));

    // Fetch subsegments for all currently checked segments and merge results
    function loadSubsegmentsForSelected() {
        let selectedSegmentIds = $('.segment-checkbox:checked').map(function () {
            return $(this).val();
        }).get();

        if (selectedSegmentIds.length === 0) {
            $('#subsegment-group').html('');
            $('#subsegment-box').hide();
            return;
        }

        // Keep track of currently checked subsegment IDs so we can restore them
        let currentlyChecked = $('.subsegment-checkbox:checked').map(function () {
            return $(this).val();
        }).get();
        // Merge with old() values
        let allChecked = [...new Set([...currentlyChecked, ...selectedSubsegments.map(String)])];

        let promises = selectedSegmentIds.map(function (segmentId) {
            let url = bySegmentUrl.replace(':segmentId', segmentId);
            return $.get(url);
        });

        $.when.apply($, promises).done(function () {
            // $.when with multiple calls wraps single results differently
            let results = promises.length === 1 ? [arguments[0]] : Array.from(arguments).map(function (a) {
                return Array.isArray(a) ? a[0] : a;
            });

            // Flatten all subsegments, deduplicate by id
            let seen = {};
            let allSubsegments = [];
            results.forEach(function (data) {
                (Array.isArray(data) ? data : [data]).forEach(function (subsegment) {
                    if (subsegment && subsegment.id && !seen[subsegment.id]) {
                        seen[subsegment.id] = true;
                        allSubsegments.push(subsegment);
                    }
                });
            });

            let html = '';
            allSubsegments.forEach(function (subsegment) {
                let checked = allChecked.includes(subsegment.id.toString()) ? 'checked' : '';
                html += `
                    <div class="col-md-3 col-sm-6 mb-2">
                        <div class="form-check">
                            <input
                                type="checkbox"
                                class="form-check-input subsegment-checkbox"
                                name="subsegment_id[]"
                                value="${subsegment.id}"
                                id="subsegment_${subsegment.id}"
                                ${checked}
                            >
                            <label class="form-check-label text-muted" for="subsegment_${subsegment.id}">
                                ${subsegment.subsegment_name}
                            </label>
                        </div>
                    </div>
                `;
            });

            $('#subsegment-group').html(html);
            if (allSubsegments.length > 0) {
                $('#subsegment-box').show();
            } else {
                $('#subsegment-box').hide();
            }
        });
    }

    // Reload subsegments whenever a segment checkbox changes
    $(document).on('change', '.segment-checkbox', function () {
        loadSubsegmentsForSelected();
    });

    // Load subsegments on validation error (old segment_id checkboxes already checked via Blade)
    @if(old('segment_id'))
        loadSubsegmentsForSelected();
    @endif

}
</script>

@endsection

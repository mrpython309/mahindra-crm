@extends('layouts/contentNavbarLayout')

@section('title', 'Create Lead')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
  <span class="text-muted fw-light">Leads /</span> Create New Lead
</h4>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Lead Information</h5>
                <a href="{{ route('leads.index') }}" class="btn btn-secondary btn-sm">
                    <i class='bx bx-arrow-back'></i> Back to List
                </a>
            </div>
            <div class="card-body">
                <!-- Progress Indicator -->
                <div class="wizard-progress mb-4">
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" role="progressbar" id="wizardProgressBar" style="width: 20%"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <small class="wizard-step" data-step="1"><strong>1. Customer</strong></small>
                        <small class="wizard-step" data-step="2">2. Additional</small>
                        <small class="wizard-step" data-step="3">3. Enquiry</small>
                        <small class="wizard-step" data-step="4">4. Purchase</small>
                        <small class="wizard-step" data-step="5">5. Assignment</small>
                    </div>
                </div>

                <form action="{{ route('leads.store') }}" method="POST" id="leadWizardForm">
                    @csrf

                    <!-- Page 1: Customer Details -->
                    <div class="wizard-page" id="page1">
                        <h5 class="mb-3" id="customer">Customer Details</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                                       value="{{ old('first_name') }}" placeholder="Enter first name" required>
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                                       value="{{ old('last_name') }}" placeholder="Enter last name" required>
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                <input type="text" name="mobile_no" class="form-control @error('mobile_no') is-invalid @enderror"
                                       value="{{ old('mobile_no') }}" placeholder="Enter mobile number" required>
                                @error('mobile_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}" placeholder="Enter email address">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Page 2: Additional Details -->
                    <div class="wizard-page" id="page2" style="display: none;">
                        <h5 class="mb-3" id="additional">Additional Details</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Age Group</label>
                                <select name="age_group" class="form-select @error('age_group') is-invalid @enderror">
                                    <option value="">Select Age Group</option>
                                    <option value="under_30" {{ old('age_group') == 'under_30' ? 'selected' : '' }}>Under 30</option>
                                    <option value="30_to_40" {{ old('age_group') == '30_to_40' ? 'selected' : '' }}>30 to 40</option>
                                    <option value="above_40" {{ old('age_group') == 'above_40' ? 'selected' : '' }}>Above 40</option>
                                </select>
                                @error('age_group')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Customer Type</label>
                                <select name="customer_type" class="form-select @error('customer_type') is-invalid @enderror">
                                    <option value="">Select Type</option>
                                    <option value="individual" {{ old('customer_type') == 'individual' ? 'selected' : '' }}>Individual</option>
                                    <option value="corporate" {{ old('customer_type') == 'corporate' ? 'selected' : '' }}>Corporate</option>
                                </select>
                                @error('customer_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Occupation Type</label>
                                <select name="occupation_type" class="form-select @error('occupation_type') is-invalid @enderror">
                                    <option value="">Select Occupation</option>
                                    @foreach($occupationTypes as $occupation)
                                        <option value="{{ $occupation }}" {{ old('occupation_type') == $occupation ? 'selected' : '' }}>
                                            {{ $occupation }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('occupation_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">State</label>
                                <select name="state" id="state_id" class="form-select @error('state') is-invalid @enderror">
                                    <option value="">Select State</option>
                                    @foreach($states as $state)
                                        <option value="{{ $state->name }}" {{ old('state') == $state->name ? 'selected' : '' }}>
                                            {{ $state->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">District</label>
                                <select name="district" id="district_id" class="form-select @error('district') is-invalid @enderror">
                                    <option value="">Select District</option>
                                </select>
                                @error('district')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">City</label>
                                <select name="city" id="city_id" class="form-select @error('city') is-invalid @enderror">
                                    <option value="">Select City</option>
                                </select>
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Pincode</label>
                                <input type="text" name="pincode" id="pincode" class="form-control @error('pincode') is-invalid @enderror"
                                       value="{{ old('pincode') }}" placeholder="Enter pincode" maxlength="6">
                                <small class="text-muted">Enter 6-digit pincode to auto-fill location</small>
                                @error('pincode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" class="form-control @error('country') is-invalid @enderror"
                                       value="{{ old('country', 'India') }}" placeholder="Enter country">
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Page 3: Enquiry Details -->
                    <div class="wizard-page" id="page3" style="display: none;">
                        <h5 class="mb-3" id="enquiry">Enquiry Details</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Enquiry Type</label>
                                <select name="enquiry_type_id" id="enquiry_type_id" class="form-select @error('enquiry_type_id') is-invalid @enderror">
                                    <option value="">Select Enquiry Type</option>
                                    @foreach($enquiryTypes as $type)
                                        <option value="{{ $type->id }}" {{ old('enquiry_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('enquiry_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Lead Source</label>
                                <select name="lead_source_id" id="lead_source_id" class="form-select @error('lead_source_id') is-invalid @enderror" disabled>
                                    <option value="">Select Enquiry Type first</option>
                                </select>
                                <small class="text-muted">Please select an Enquiry Type first</small>
                                @error('lead_source_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Lead Response</label>
                                <select name="lead_response_id" id="lead_response_id" class="form-select @error('lead_response_id') is-invalid @enderror">
                                    <option value="">Select Lead Response</option>
                                    @foreach($statusSources as $response)
                                        <option
                                            value="{{ $response->id }}"
                                            data-response-name="{{ strtolower(trim($response->response)) }}"
                                            {{ old('lead_response_id') == $response->id ? 'selected' : '' }}
                                        >
                                            {{ $response->response }}
                                            @if($response->leadStatus)
                                                ({{ ucfirst($response->leadStatus->name) }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('lead_response_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4" id="test_drive_at_wrap" style="display: none;">
                                <label class="form-label">Test Drive Date & Time <span class="text-danger">*</span></label>
                                <input
                                    type="datetime-local"
                                    name="test_drive_at"
                                    id="test_drive_at"
                                    class="form-control @error('test_drive_at') is-invalid @enderror"
                                    value="{{ old('test_drive_at') }}"
                                >
                                @error('test_drive_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Segment</label>
                                <select name="segment_id" id="segment_id" class="form-select @error('segment_id') is-invalid @enderror">
                                    <option value="">Select Segment</option>
                                    @foreach($segments as $segment)
                                        <option value="{{ $segment->id }}" {{ old('segment_id') == $segment->id ? 'selected' : '' }}>
                                            {{ $segment->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('segment_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Sub Segment</label>
                                <select name="sub_segment_id" id="sub_segment_id" class="form-select @error('sub_segment_id') is-invalid @enderror">
                                    <option value="">Select Sub Segment</option>
                                </select>
                                @error('sub_segment_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Model</label>
                                <select name="vehicle_model_id" id="vehicle_model_id" class="form-select @error('vehicle_model_id') is-invalid @enderror">
                                    <option value="">Select Model</option>
                                </select>
                                @error('vehicle_model_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror"
                                       value="{{ old('quantity', 1) }}" min="1" placeholder="Enter quantity">
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Variants</label>
                                <select name="variant_ids[]" id="variant_ids" class="form-select @error('variant_ids') is-invalid @enderror" multiple>
                                </select>
                                <small class="text-muted">Hold Ctrl/Cmd to select multiple variants</small>
                                @error('variant_ids')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Fuel Types</label>
                                <select name="fuel_type_ids[]" id="fuel_type_ids" class="form-select @error('fuel_type_ids') is-invalid @enderror" multiple>
                                </select>
                                @error('fuel_type_ids')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Transmissions</label>
                                <select name="transmission_ids[]" id="transmission_ids" class="form-select @error('transmission_ids') is-invalid @enderror" multiple>
                                </select>
                                @error('transmission_ids')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Colours</label>
                                <select name="colour_ids[]" id="colour_ids" class="form-select @error('colour_ids') is-invalid @enderror" multiple>
                                </select>
                                @error('colour_ids')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Page 4: Purchase Details -->
                    <div class="wizard-page" id="page4" style="display: none;">
                        <h5 class="mb-3" id="purchase">Purchase Details</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Purchase Type</label>
                                <select name="purchase_type" id="purchase_type" class="form-select @error('purchase_type') is-invalid @enderror">
                                    <option value="">Select Purchase Type</option>
                                    <option value="first_time_buy" {{ old('purchase_type') == 'first_time_buy' ? 'selected' : '' }}>First Time Buy</option>
                                    <option value="exchange_buy" {{ old('purchase_type') == 'exchange_buy' ? 'selected' : '' }}>Exchange Buy</option>
                                    <option value="additional_buy" {{ old('purchase_type') == 'additional_buy' ? 'selected' : '' }}>Additional Buy</option>
                                    <option value="scrappage" {{ old('purchase_type') == 'scrappage' ? 'selected' : '' }}>Scrappage</option>
                                </select>
                                @error('purchase_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Exchange Vehicle Details (Conditional) -->
                            <div class="col-md-12" id="exchange_fields" style="display: none;">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="mb-3">Exchange Vehicle Details</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Year Model</label>
                                                <select name="exchange_manufacture_year" class="form-select @error('exchange_manufacture_year') is-invalid @enderror">
                                                    <option value="">Select Year</option>
                                                    @for($year = date('Y') + 1; $year >= 1980; $year--)
                                                        <option value="{{ $year }}" {{ old('exchange_manufacture_year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                                                    @endfor
                                                </select>
                                                @error('exchange_manufacture_year')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Make</label>
                                                <select name="exchange_make_id" id="exchange_make_id" class="form-select @error('exchange_make_id') is-invalid @enderror">
                                                    <option value="">Select Make</option>
                                                    @foreach($makes as $make)
                                                        <option value="{{ $make->id }}" {{ old('exchange_make_id') == $make->id ? 'selected' : '' }}>
                                                            {{ $make->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('exchange_make_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Model</label>
                                                <select name="exchange_brand_model_id" id="exchange_brand_model_id" class="form-select @error('exchange_brand_model_id') is-invalid @enderror">
                                                    <option value="">Select Make first</option>
                                                </select>
                                                @error('exchange_brand_model_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Fuel</label>
                                                <select name="exchange_fuel_type_id" class="form-select @error('exchange_fuel_type_id') is-invalid @enderror">
                                                    <option value="">Select Fuel Type</option>
                                                    @foreach($fuelTypes as $fuelType)
                                                        <option value="{{ $fuelType->id }}" {{ old('exchange_fuel_type_id') == $fuelType->id ? 'selected' : '' }}>
                                                            {{ $fuelType->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('exchange_fuel_type_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">KMs</label>
                                                <input type="text" name="exchange_kms" class="form-control @error('exchange_kms') is-invalid @enderror"
                                                       value="{{ old('exchange_kms') }}" placeholder="Enter KMs">
                                                @error('exchange_kms')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">No of Owner</label>
                                                <input type="number" name="exchange_no_of_owners" class="form-control @error('exchange_no_of_owners') is-invalid @enderror"
                                                       value="{{ old('exchange_no_of_owners') }}" min="0" placeholder="Enter number of owners">
                                                @error('exchange_no_of_owners')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Likely Purchase</label>
                                <select name="lead_type_id" class="form-select @error('lead_type_id') is-invalid @enderror">
                                    <option value="">Select Likely Purchase</option>
                                    @foreach($leadTypes as $type)
                                        <option value="{{ $type->id }}" {{ old('lead_type_id') == $type->id ? 'selected' : '' }}>
                                            @if($type->likely_purchase)
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
                                            @else
                                                {{ $type->name }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('lead_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Next Follow-up Date</label>
                                <input type="date" name="next_follow_up" class="form-control @error('next_follow_up') is-invalid @enderror"
                                       value="{{ old('next_follow_up') }}"
                                       min="{{ date('Y-m-d') }}">
                                @error('next_follow_up')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Page 5: Assignment -->
                    <div class="wizard-page" id="page5" style="display: none;">
                        <h5 class="mb-3" id="assignment">Assignment</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Company</label>
                                <select name="company_id" id="company_id" class="form-select @error('company_id') is-invalid @enderror">
                                    <option value="">Select Company</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                            {{ $company->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('company_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Location</label>
                                <select name="location_id" id="location_id" class="form-select @error('location_id') is-invalid @enderror">
                                    <option value="">Select Location</option>
                                </select>
                                @error('location_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    {{-- "Inactive" is used as Draft; default to Active so leads are not auto-saved as draft --}}
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Draft</option>
                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="closed" {{ old('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Additional Comments</label>
                                <textarea name="additional_comments" class="form-control @error('additional_comments') is-invalid @enderror"
                                          rows="4" placeholder="Enter any additional comments">{{ old('additional_comments') }}</textarea>
                                @error('additional_comments')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="row mt-4">
                        <div class="col-12 d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" id="prevBtn" style="display: none;">
                                <i class='bx bx-chevron-left'></i> Previous
                            </button>
                            <div class="d-flex gap-2">
                                {{-- Save as Draft is always available so user can draft from any step --}}
                                <button type="button" class="btn btn-warning" id="saveDraftBtn">
                                    <i class='bx bx-save'></i> Save as Draft
                                </button>
                            </div>
                            <button type="button" class="btn btn-primary" id="nextBtn">
                                Next <i class='bx bx-chevron-right'></i>
                            </button>
                            <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
                                <i class='bx bx-save'></i> Create Lead
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-style')
<style>
.wizard-progress .wizard-step {
    color: #999;
    cursor: default;
}
.wizard-progress .wizard-step.active {
    color: #696cff;
    font-weight: bold;
}
.wizard-progress .wizard-step.completed {
    color: #28a745;
}
</style>
@endsection

@section('page-script')
<script>
// Wait for jQuery to be available (Vite loads asynchronously)
(function checkJQuery() {
    if (typeof jQuery !== 'undefined') {
        // jQuery is ready, now load Select2 if not already loaded
        if (typeof jQuery.fn.select2 === 'undefined') {
            // Load Select2 dynamically
            var script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js';
            script.onload = function() {
                // Select2 loaded, initialize page
                initializePage();
            };
            document.head.appendChild(script);
        } else {
            // Select2 already loaded
            initializePage();
        }
    } else {
        // jQuery not ready yet, wait
        setTimeout(checkJQuery, 50);
    }
})();

function initializePage() {
    // Setup CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    let currentPage = 1;
    const totalPages = 5;

    // If URL hash points to a specific section, jump directly to that page
    switch (window.location.hash) {
        case '#customer':
            currentPage = 1;
            break;
        case '#additional':
            currentPage = 2;
            break;
        case '#enquiry':
            currentPage = 3;
            break;
        case '#purchase':
            currentPage = 4;
            break;
        case '#assignment':
            currentPage = 5;
            break;
    }

    // Initialize wizard
    showPage(currentPage);

    // Initialize Select2 for multi-select fields
    $('#variant_ids').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select variants',
        allowClear: true,
        width: '100%'
    });

    $('#fuel_type_ids').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select fuel types',
        allowClear: true,
        width: '100%'
    });

    $('#transmission_ids').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select transmissions',
        allowClear: true,
        width: '100%'
    });

    $('#colour_ids').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select colours',
        allowClear: true,
        width: '100%'
    });

    // Save as Draft from any step – set status to Draft and submit
    $('#saveDraftBtn').click(function() {
        const $statusSelect = $('select[name="status"]');
        if ($statusSelect.length) {
            $statusSelect.val('inactive');
        } else {
            // Fallback: inject hidden status field as inactive
            if ($('#leadWizardForm input[name="status"]').length === 0) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'status',
                    value: 'inactive'
                }).appendTo('#leadWizardForm');
            } else {
                $('#leadWizardForm input[name="status"]').val('inactive');
            }
        }

        $('#leadWizardForm').submit();
    });

    // Next button
    $('#nextBtn').click(function() {
        if (validatePage(currentPage)) {
            currentPage++;
            showPage(currentPage);
        }
    });

    // Previous button
    $('#prevBtn').click(function() {
        currentPage--;
        showPage(currentPage);
    });

    // Show specific page
    function showPage(page) {
        // Hide all pages
        $('.wizard-page').hide();

        // Show current page
        $('#page' + page).show();

        // Update progress bar
        const progress = (page / totalPages) * 100;
        $('#wizardProgressBar').css('width', progress + '%');

        // Update step indicators
        $('.wizard-step').removeClass('active completed');
        $('.wizard-step').each(function() {
            const stepNum = parseInt($(this).data('step'));
            if (stepNum < page) {
                $(this).addClass('completed');
            } else if (stepNum === page) {
                $(this).addClass('active');
            }
        });

        // Update buttons
        if (page === 1) {
            $('#prevBtn').hide();
        } else {
            $('#prevBtn').show();
        }

        if (page === totalPages) {
            $('#nextBtn').hide();
            $('#submitBtn').show();
        } else {
            $('#nextBtn').show();
            $('#submitBtn').hide();
        }
    }

    // Basic validation for required fields on current page
    function validatePage(page) {
        let isValid = true;
        $('#page' + page + ' input[required], #page' + page + ' select[required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        if (!isValid) {
            alert('Please fill in all required fields before proceeding.');
        }

        return isValid;
    }

    // Function to restore old values after validation errors
    function restoreOldValues() {
        const oldSegmentId = '{{ old("segment_id") }}';
        const oldSubSegmentId = '{{ old("sub_segment_id") }}';
        const oldVehicleModelId = '{{ old("vehicle_model_id") }}';
        const oldVariantIds = {!! json_encode(old('variant_ids', [])) !!};
        const oldFuelTypeIds = {!! json_encode(old('fuel_type_ids', [])) !!};
        const oldTransmissionIds = {!! json_encode(old('transmission_ids', [])) !!};
        const oldColourIds = {!! json_encode(old('colour_ids', [])) !!};

        if (oldSegmentId) {
            $('#segment_id').val(oldSegmentId);

            // Load sub-segments for the old segment
            $.get('/leads/ajax/sub-segments/' + oldSegmentId, function(data) {
                $('#sub_segment_id').html('<option value="">Select Sub Segment</option>');
                data.forEach(function(subSegment) {
                    $('#sub_segment_id').append('<option value="' + subSegment.id + '">' + subSegment.name + '</option>');
                });

                if (oldSubSegmentId) {
                    $('#sub_segment_id').val(oldSubSegmentId);

                    // Load vehicle models for the old sub-segment
                    $.get('/leads/ajax/vehicle-models/' + oldSubSegmentId, function(data) {
                        $('#vehicle_model_id').html('<option value="">Select Model</option>');
                        data.forEach(function(model) {
                            $('#vehicle_model_id').append('<option value="' + model.id + '">' + model.name + '</option>');
                        });

                        if (oldVehicleModelId) {
                            $('#vehicle_model_id').val(oldVehicleModelId);

                            // Load variants for the old vehicle model
                            $.get('/leads/ajax/variants/' + oldVehicleModelId, function(data) {
                                $('#variant_ids').html('');
                                data.forEach(function(variant) {
                                    const selected = oldVariantIds.includes(variant.id) ? 'selected' : '';
                                    $('#variant_ids').append('<option value="' + variant.id + '" ' + selected + '>' + variant.name + '</option>');
                                });

                                // Load fuel types, transmissions, and colours if variants are selected
                                if (oldVariantIds.length > 0) {
                                    $.post('/leads/ajax/fuel-types', { variant_ids: oldVariantIds }, function(data) {
                                        $('#fuel_type_ids').html('');
                                        data.forEach(function(fuelType) {
                                            const selected = oldFuelTypeIds.includes(fuelType.id) ? 'selected' : '';
                                            $('#fuel_type_ids').append('<option value="' + fuelType.id + '" ' + selected + '>' + fuelType.name + '</option>');
                                        });
                                    });

                                    $.post('/leads/ajax/transmissions', { variant_ids: oldVariantIds }, function(data) {
                                        $('#transmission_ids').html('');
                                        data.forEach(function(transmission) {
                                            const selected = oldTransmissionIds.includes(transmission.id) ? 'selected' : '';
                                            $('#transmission_ids').append('<option value="' + transmission.id + '" ' + selected + '>' + transmission.name + '</option>');
                                        });
                                    });

                                    $.post('/leads/ajax/colours', { variant_ids: oldVariantIds }, function(data) {
                                        $('#colour_ids').html('');
                                        data.forEach(function(colour) {
                                            const selected = oldColourIds.includes(colour.id) ? 'selected' : '';
                                            $('#colour_ids').append('<option value="' + colour.id + '" ' + selected + '>' + colour.name + '</option>');
                                        });
                                    });
                                }
                            });
                        }
                    });
                }
            });
        }
    }

    // Restore old values on page load (after validation errors)
    restoreOldValues();

    // Enquiry Type -> Lead Source
    $('#enquiry_type_id').change(function() {
        const enquiryTypeId = $(this).val();
        const $leadSource = $('#lead_source_id');

        if (!enquiryTypeId) {
            // No enquiry type selected - disable and reset lead source
            $leadSource.prop('disabled', true).html('<option value=\"\">Select Enquiry Type first</option>');
            return;
        }

        // Enable lead source dropdown
        $leadSource.prop('disabled', false).html('<option value=\"\">Loading...</option>');

        $.get('/leads/ajax/lead-sources/' + enquiryTypeId, function(response) {
            if (response.success && response.lead_sources) {
                $leadSource.html('<option value=\"\">Select Source</option>');
                response.lead_sources.forEach(function(source) {
                    $leadSource.append(
                        '<option value=\"' + source.id + '\">' + source.campaign + '</option>'
                    );
                });
            } else {
                $leadSource.html('<option value=\"\">No sources available</option>');
            }
        }).fail(function() {
            console.error('Failed to load lead sources for enquiry type');
            $leadSource.html('<option value=\"\">Error loading sources</option>');
        });
    });

    // Load lead sources on page load if enquiry type is already selected (for validation errors)
    const oldEnquiryTypeId = '{{ old("enquiry_type_id") }}';
    if (oldEnquiryTypeId) {
        $('#enquiry_type_id').val(oldEnquiryTypeId).trigger('change');

        // After loading sources, restore old lead source selection
        setTimeout(function() {
            const oldLeadSourceId = '{{ old("lead_source_id") }}';
            if (oldLeadSourceId) {
                $('#lead_source_id').val(oldLeadSourceId);
            }
        }, 500);
    }

    function getCurrentDateTimeLocal() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    function toggleTestDriveField() {
        const selectedOption = $('#lead_response_id option:selected');
        const responseName = (selectedOption.data('response-name') || '')
            .toString()
            .toLowerCase()
            .replace(/[\s_-]+/g, ' ')
            .trim();
        const isTestDriveScheduled = responseName.includes('test drive') && responseName.includes('scheduled');
        const $testDriveInput = $('#test_drive_at');

        if (isTestDriveScheduled) {
            $('#test_drive_at_wrap').show();
            $testDriveInput.prop('required', true);
            $testDriveInput.attr('min', getCurrentDateTimeLocal());
        } else {
            $('#test_drive_at_wrap').hide();
            $testDriveInput.prop('required', false);
            $testDriveInput.val('');
        }
    }

    $('#lead_response_id').on('change', toggleTestDriveField);
    toggleTestDriveField();

    // Cascading Dropdowns - Segment -> Sub Segment
    $('#segment_id').change(function() {
        const segmentId = $(this).val();
        $('#sub_segment_id').html('<option value="">Select Sub Segment</option>');
        $('#vehicle_model_id').html('<option value="">Select Model</option>');
        $('#variant_ids').html('');

        if (segmentId) {
            $.get('/leads/ajax/sub-segments/' + segmentId, function(data) {
                data.forEach(function(subSegment) {
                    $('#sub_segment_id').append('<option value="' + subSegment.id + '">' + subSegment.name + '</option>');
                });
            });
        }
    });

    // Sub Segment -> Model
    $('#sub_segment_id').change(function() {
        const subSegmentId = $(this).val();
        $('#vehicle_model_id').html('<option value="">Select Model</option>');
        $('#variant_ids').html('');

        if (subSegmentId) {
            $.get('/leads/ajax/vehicle-models/' + subSegmentId, function(data) {
                data.forEach(function(model) {
                    $('#vehicle_model_id').append('<option value="' + model.id + '">' + model.name + '</option>');
                });
            });
        }
    });

    // Model -> Variants
    $('#vehicle_model_id').change(function() {
        const modelId = $(this).val();
        $('#variant_ids').html('');

        if (modelId) {
            $.get('/leads/ajax/variants/' + modelId, function(data) {
                data.forEach(function(variant) {
                    $('#variant_ids').append('<option value="' + variant.id + '">' + variant.name + '</option>');
                });
            });
        }
    });

    // Variants -> Fuel Types, Transmissions, Colours
    $('#variant_ids').change(function() {
        const variantIds = $(this).val();

        if (variantIds && variantIds.length > 0) {
            // Fuel Types
            $.post('/leads/ajax/fuel-types', { variant_ids: variantIds }, function(data) {
                $('#fuel_type_ids').select2('destroy');
                $('#fuel_type_ids').html('');
                data.forEach(function(fuelType) {
                    $('#fuel_type_ids').append('<option value="' + fuelType.id + '">' + fuelType.name + '</option>');
                });
                $('#fuel_type_ids').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Select fuel types',
                    allowClear: true,
                    width: '100%'
                });
            });

            // Transmissions
            $.post('/leads/ajax/transmissions', { variant_ids: variantIds }, function(data) {
                $('#transmission_ids').select2('destroy');
                $('#transmission_ids').html('');
                data.forEach(function(transmission) {
                    $('#transmission_ids').append('<option value="' + transmission.id + '">' + transmission.name + '</option>');
                });
                $('#transmission_ids').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Select transmissions',
                    allowClear: true,
                    width: '100%'
                });
            });

            // Colours
            $.post('/leads/ajax/colours', { variant_ids: variantIds }, function(data) {
                $('#colour_ids').select2('destroy');
                $('#colour_ids').html('');
                data.forEach(function(colour) {
                    $('#colour_ids').append('<option value="' + colour.id + '">' + colour.name + '</option>');
                });
                $('#colour_ids').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Select colours',
                    allowClear: true,
                    width: '100%'
                });
            });
        } else {
            $('#fuel_type_ids, #transmission_ids, #colour_ids').html('');
        }
    });

    // Purchase Type -> Exchange Fields
    $('#purchase_type').change(function() {
        if ($(this).val() === 'exchange_buy') {
            $('#exchange_fields').slideDown();
        } else {
            $('#exchange_fields').slideUp();
        }
    });

    // Exchange Make -> Brand Model
    $('#exchange_make_id').change(function() {
        const makeId = $(this).val();
        $('#exchange_brand_model_id').html('<option value="">Select Model</option>');

        if (makeId) {
            $.get('/leads/ajax/brand-models/' + makeId, function(response) {
                if (response.success && response.brand_models) {
                    response.brand_models.forEach(function(model) {
                        $('#exchange_brand_model_id').append('<option value="' + model.id + '">' + model.name + '</option>');
                    });
                }
            });
        }
    });

    // Company -> Location
    $('#company_id').change(function() {
        const companyId = $(this).val();
        $('#location_id').html('<option value="">Select Location</option>');

        if (companyId) {
            $.get('/leads/ajax/locations/' + companyId, function(data) {
                data.forEach(function(location) {
                    $('#location_id').append('<option value="' + location.id + '">' + location.name + (location.state ? ', ' + location.state : '') + '</option>');
                });
            });
        }
    });

    // Cascading Dropdowns - State -> District
    $('#state_id').change(function() {
        const stateName = $(this).val();
        $('#district_id').html('<option value="">Select District</option>');
        $('#city_id').html('<option value="">Select City</option>');

        if (stateName) {
            // Get state ID from the selected option
            const stateOption = $(this).find('option:selected');
            const stateId = stateOption.data('id') || stateName;

            $.get('/leads/ajax/districts/' + encodeURIComponent(stateId), function(data) {
                data.forEach(function(district) {
                    $('#district_id').append('<option value="' + district.name + '" data-id="' + district.id + '">' + district.name + '</option>');
                });
            }).fail(function() {
                console.error('Failed to load districts');
            });
        }
    });

    // District -> City
    $('#district_id').change(function() {
        const districtName = $(this).val();
        $('#city_id').html('<option value="">Select City</option>');

        if (districtName) {
            const districtOption = $(this).find('option:selected');
            const districtId = districtOption.data('id') || districtName;

            $.get('/leads/ajax/cities/' + encodeURIComponent(districtId), function(data) {
                data.forEach(function(city) {
                    $('#city_id').append('<option value="' + city.name + '">' + city.name + '</option>');
                });
            }).fail(function() {
                console.error('Failed to load cities');
            });
        }
    });

    // Restore old values for state/district/city on page load
    const oldState = '{{ old("state") }}';
    const oldDistrict = '{{ old("district") }}';
    const oldCity = '{{ old("city") }}';

    if (oldState) {
        $('#state_id').val(oldState).trigger('change');

        // Wait for districts to load, then set old district
        setTimeout(function() {
            if (oldDistrict) {
                $('#district_id').val(oldDistrict).trigger('change');

                // Wait for cities to load, then set old city
                setTimeout(function() {
                    if (oldCity) {
                        $('#city_id').val(oldCity);
                    }
                }, 500);
            }
        }, 500);
    }

    // Initialize exchange fields if old value exists
    if ('{{ old("purchase_type") }}' === 'exchange_buy') {
        $('#exchange_fields').show();
    }

    // Pincode -> Location Auto-fill (restrict manual change while valid pincode is set)
    let pincodeTimeout;
    $('#pincode').on('input', function() {
        const pincode = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(pincode);

        clearTimeout(pincodeTimeout);

        // If pincode is incomplete, clear and re-enable manual selection
        if (pincode.length !== 6) {
            $('#state_id, #district_id, #city_id').prop('disabled', false);
            return;
        }

        pincodeTimeout = setTimeout(function() {
            $.get('/leads/ajax/location-by-pincode/' + pincode, function(response) {
                if (response.success) {
                    // Set state
                    if (response.state) {
                        $('#state_id').val(response.state.name).trigger('change');

                        // Wait for districts to load, then set district
                        setTimeout(function() {
                            if (response.district) {
                                $('#district_id').val(response.district.name).trigger('change');

                                // Wait for cities to load, then set city
                                setTimeout(function() {
                                    if (response.city) {
                                        $('#city_id').val(response.city.name);
                                    }
                                }, 500);
                            }
                        }, 500);
                    }

                    // Disable manual modification once auto-filled from pincode
                    $('#state_id, #district_id, #city_id').prop('disabled', true);
                } else {
                    // Allow manual selection if location not found
                    if (response.message) {
                        console.log(response.message);
                    }
                    $('#state_id, #district_id, #city_id').prop('disabled', false);
                }
            }).fail(function() {
                console.log('Unable to fetch location by pincode');
                $('#state_id, #district_id, #city_id').prop('disabled', false);
            });
        }, 500); // Wait 500ms after user stops typing
    });
} // End initializePage
</script>
@endsection

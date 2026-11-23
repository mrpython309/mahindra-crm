<div class="row g-4">
    <!-- Contact Information -->
    <div class="col-md-6">
        <div class="card h-100 border-0 shadow-3d-light bg-white">
            <div class="card-body p-4">
                <h6 class="mb-4 text-primary fw-bold"><i class="bx bx-user me-2"></i>Contact Information</h6>
                <table class="table table-borderless m-0">
                    <tr>
                        <td class="fw-bold text-muted py-2" width="160">Name:</td>
                        <td class="py-2 text-charcoal">{{ $lead->first_name }} {{ $lead->last_name }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-muted py-2">Mobile Number:</td>
                        <td class="py-2 text-charcoal">{{ $lead->mobile_no ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-muted py-2">Company:</td>
                        <td class="py-2 text-charcoal">{{ $lead->company?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-muted py-2">Email:</td>
                        <td class="py-2 text-charcoal">{{ $lead->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-muted py-2">Gender:</td>
                        <td class="py-2 text-charcoal">{{ $lead->gender ? ucfirst($lead->gender) : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-muted py-2">Age Group:</td>
                        <td class="py-2 text-charcoal">{{ $lead->age_group ? str_replace('_', ' ', ucwords($lead->age_group, '_')) : '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Address Information -->
    <div class="col-md-6">
        <div class="card h-100 border-0 shadow-3d-light bg-white">
            <div class="card-body p-4">
                <h6 class="mb-4 text-primary fw-bold"><i class="bx bx-map-pin me-2"></i>Address Information</h6>
                <table class="table table-borderless m-0">
                    <tr>
                        <td class="fw-bold text-muted py-2" width="160">City:</td>
                        <td class="py-2 text-charcoal">{{ $lead->city ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-muted py-2">District:</td>
                        <td class="py-2 text-charcoal">{{ $lead->district ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-muted py-2">State:</td>
                        <td class="py-2 text-charcoal">{{ $lead->state ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-muted py-2">Pincode:</td>
                        <td class="py-2 text-charcoal">{{ $lead->pincode ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-muted py-2">Country:</td>
                        <td class="py-2 text-charcoal">{{ $lead->country ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Lead Details -->
    <div class="col-md-6">
        <div class="card h-100 border-0 shadow-3d-light bg-white">
            <div class="card-body p-4">
                <h6 class="mb-4 text-primary fw-bold"><i class="bx bx-info-circle me-2"></i>Lead Details</h6>
                <table class="table table-borderless m-0">
                    <tr>
                        <td class="fw-bold text-muted py-2" width="160">Lead Type:</td>
                        <td class="py-2 text-charcoal">{{ $lead->leadType->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-muted py-2">Lead Source:</td>
                        <td class="py-2 text-charcoal">{{ $lead->leadSource->campaign ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-muted py-2">Lead Stage:</td>
                        <td class="py-2">
                            @if($lead->leadStatus)
                                <span class="badge bg-label-primary">{{ $lead->leadStatus->name }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-muted py-2">Lead Response:</td>
                        <td class="py-2 text-charcoal">
                            @if($lead->leadResponse)
                                {{ $lead->leadResponse->response }}
                                @if($lead->leadResponse->leadStatus)
                                    ({{ $lead->leadResponse->leadStatus->name }})
                                @endif
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-muted py-2">Enquiry Type:</td>
                        <td class="py-2 text-charcoal">{{ $lead->enquiryType->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-muted py-2">Test Drive Scheduled:</td>
                        <td class="py-2 text-charcoal">{{ $lead->test_drive_at ? $lead->test_drive_at->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-muted py-2">Next Follow Up:</td>
                        <td class="py-2 text-charcoal">{{ $lead->next_follow_up ? $lead->next_follow_up->format('d/m/Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-muted py-2">Tele-caller:</td>
                        <td class="py-2 text-charcoal">
                            @php $teleCaller = $lead->primaryTeleCaller(); @endphp
                            @if($teleCaller)
                                {{ $teleCaller->name }}
                                @if($teleCaller->email)
                                    <small class="text-muted">({{ $teleCaller->email }})</small>
                                @endif
                            @else
                                <span class="text-muted">Unassigned</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-muted py-2">Sales Consultant:</td>
                        <td class="py-2 text-charcoal">
                            @php $salesConsultant = $lead->primarySalesConsultant(); @endphp
                            @if($salesConsultant)
                                {{ $salesConsultant->name }}
                                @if($salesConsultant->email)
                                    <small class="text-muted">({{ $salesConsultant->email }})</small>
                                @endif
                            @else
                                <span class="text-muted">Not assigned</span>
                            @endif
                        </td>
                    </tr>
                    @if($lead->location)
                    <tr>
                        <td class="fw-bold text-muted py-2">Location:</td>
                        <td class="py-2 text-charcoal">{{ $lead->location->name ?? '-' }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Additional Information -->
    <div class="col-md-6">
        <div class="card h-100 border-0 shadow-3d-light bg-white">
            <div class="card-body p-4">
                <h6 class="mb-4 text-primary fw-bold"><i class="bx bx-file me-2"></i>Additional Information</h6>
                <table class="table table-borderless m-0">
                    <tr>
                        <td class="fw-bold text-muted py-2" width="160">Additional Comments:</td>
                        <td class="py-2 text-charcoal">{{ $lead->additional_comments ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-muted py-2">Created By:</td>
                        <td class="py-2 text-charcoal">{{ $lead->creator->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-muted py-2">Created At:</td>
                        <td class="py-2 text-charcoal">{{ $lead->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-muted py-2">Last Updated:</td>
                        <td class="py-2 text-charcoal">{{ $lead->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

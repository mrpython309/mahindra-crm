<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadType;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\LeadImportBatch;
use App\Models\EnquiryType;
use App\Models\Segment;
use App\Models\SubSegment;
use App\Models\VehicleModel;
use App\Models\Variant;
use App\Models\FuelType;
use App\Models\Transmission;
use App\Models\Colour;
use App\Models\Company;
use App\Models\Make;
use App\Models\BrandModel;
use App\Models\Location;
use App\Models\DealerLocation;
use App\Models\State;
use App\Models\District;
use App\Models\City;
use App\Models\User;
use App\Models\Employee;
use App\Models\StatusSource;
use App\Models\CustomLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\ValidationException;

class LeadController extends Controller
{
    use AuthorizesRequests;

    /**
     * Set lead_status_id and record status from the selected lead response master (status_sources),
     * consistent with {@see \App\Http\Controllers\Lead\LeadCallController::syncLeadLikelyPurchase()}.
     *
     * @param  array<string, mixed>  $validated
     */
    private function applyLeadResponseMasterMapping(array &$validated): void
    {
        $responseId = isset($validated['lead_response_id']) ? (int) $validated['lead_response_id'] : null;
        if (!$responseId) {
            return;
        }

        $statusSource = StatusSource::with('leadStatus')->find($responseId);
        if (!$statusSource) {
            return;
        }

        if ($statusSource->lead_status_id) {
            $validated['lead_status_id'] = $statusSource->lead_status_id;
        }

        if ($statusSource->leadStatus && strtolower((string) $statusSource->leadStatus->name) === 'closed') {
            $validated['status'] = 'closed';
        }
    }

    private function isTestDriveScheduledResponse(?int $statusSourceId): bool
    {
        if (!$statusSourceId) {
            return false;
        }

        $response = StatusSource::where('id', $statusSourceId)->value('response');
        $normalizedResponse = strtolower(trim((string) $response));
        $normalizedResponse = preg_replace('/[\s_-]+/', ' ', $normalizedResponse ?? '');

        return str_contains($normalizedResponse, 'test drive')
            && str_contains($normalizedResponse, 'scheduled');
    }

    private function getScopedCompaniesForUser(User $user)
    {
        if ($user->hasRole('superadmin')) {
            return Company::active()->get();
        }

        $companyId = $user->employee?->company_id;
        if (!$companyId) {
            return collect();
        }

        return Company::active()->where('id', $companyId)->get();
    }

    /**
     * Accept YYYY-MM-DD only for lead list date filters (matches DATE column / import lead_date).
     */
    private function normalizeLeadListDateInput(mixed $value): ?string
    {
        if (!is_string($value) || $value === '') {
            return null;
        }

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : null;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $authUser = Auth::user();
            $leadTypes = LeadType::all();
            $leadSources = LeadSource::all();
            $leadStatuses = LeadStatus::all();
            $companies = $this->getScopedCompaniesForUser($authUser);
            $statusSources = StatusSource::with('leadStatus')->get();
            $enquiryTypes = EnquiryType::all();

            return view('lead.index', compact('leadTypes', 'leadSources', 'leadStatuses', 'companies', 'statusSources', 'enquiryTypes'));
        } catch (Exception $e) {
            Log::error('Error loading leads index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to load leads. Please try again later.');
        }
    }

    /**
     * Get paginated lead data for list/search (AJAX).
     */
    public function getData(Request $request)
    {
        $authUser = Auth::user();

        $query = Lead::with([
            'leadType',
            'leadSource',
            'teleCallers',
            'salesConsultants',
            'company',
        ]);

        // Role-based lead visibility
        if (!$authUser->hasRole('superadmin')) {
            if ($authUser->hasRole('tele-caller')) {
                $query->whereHas('assignments', function ($q) use ($authUser) {
                    $q->where('user_id', $authUser->id)->where('assignment_type', 'tele_caller');
                });
            } elseif ($authUser->hasRole('manager')) {
                $allowedUserIds = $this->getAllowedUserIds($authUser);
                $query->whereHas('assignments', function ($q) use ($allowedUserIds) {
                    $q->whereIn('user_id', $allowedUserIds);
                });
            } elseif ($authUser->hasRole('sales-consultant')) {
                $query->whereHas('assignments', function ($q) use ($authUser) {
                    $q->where('user_id', $authUser->id);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // All record statuses shown; active rows sort first, then by recency.
        $table = (new Lead)->getTable();

        // Exclude duplicate leads
        $duplicateStatusId = LeadStatus::where('name', 'duplicate')->value('id');
        if ($duplicateStatusId) {
            $query->where(function ($q) use ($duplicateStatusId) {
                $q->whereNull('lead_status_id')->orWhere('lead_status_id', '!=', $duplicateStatusId);
            });
        }

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('mobile_no', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhereHas('leadType', fn($r) => $r->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('leadSource', fn($r) => $r->where('campaign', 'like', "%{$search}%"))
                  ->orWhereHas('company', fn($r) => $r->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('lead_type_id')) {
            $query->where('lead_type_id', $request->lead_type_id);
        }

        if ($request->filled('lead_source_id')) {
            $query->where('lead_source_id', $request->lead_source_id);
        }

        if ($request->filled('lead_response_id')) {
            $query->where('lead_response_id', $request->lead_response_id);
        }

        if ($request->filled('enquiry_type_id')) {
            $query->where('enquiry_type_id', $request->enquiry_type_id);
        }

        $dateFrom = $this->normalizeLeadListDateInput($request->input('date_from'));
        $dateTo = $this->normalizeLeadListDateInput($request->input('date_to'));
        if ($dateFrom && $dateTo && strcmp($dateFrom, $dateTo) > 0) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $leads = $query
            ->orderByRaw("CASE WHEN {$table}.status = ? THEN 0 ELSE 1 END", ['active'])
            ->orderByDesc("{$table}.created_at")
            ->orderByDesc("{$table}.id")
            ->paginate(25)
            ->withPath(route('leads.index'));

        return response()->json([
            'success'    => true,
            'html'       => view('lead.partials.table-rows', compact('leads'))->render(),
            'pagination' => view('lead.partials.pagination', compact('leads'))->render(),
        ]);
    }

    /**
     * Export leads as CSV (superadmin only).
     */
    public function export(Request $request)
    {
        $authUser = Auth::user();
        abort_unless($authUser && $authUser->hasRole('superadmin'), 403);

        $query = Lead::with([
            'leadType',
            'leadSource',
            'leadResponse',
            'company',
            'teleCallers',
            'salesConsultants',
        ]);

        $exportTable = (new Lead)->getTable();

        // Same scope as lead list: all statuses; active first.
        $duplicateStatusId = LeadStatus::where('name', 'duplicate')->value('id');
        if ($duplicateStatusId) {
            $query->where(function ($q) use ($duplicateStatusId) {
                $q->whereNull('lead_status_id')->orWhere('lead_status_id', '!=', $duplicateStatusId);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('mobile_no', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhereHas('leadType', fn($r) => $r->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('leadSource', fn($r) => $r->where('campaign', 'like', "%{$search}%"))
                    ->orWhereHas('company', fn($r) => $r->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('lead_type_id')) {
            $query->where('lead_type_id', $request->lead_type_id);
        }

        if ($request->filled('lead_source_id')) {
            $query->where('lead_source_id', $request->lead_source_id);
        }

        if ($request->filled('lead_response_id')) {
            $query->where('lead_response_id', $request->lead_response_id);
        }

        if ($request->filled('enquiry_type_id')) {
            $query->where('enquiry_type_id', $request->enquiry_type_id);
        }

        $dateFrom = $this->normalizeLeadListDateInput($request->input('date_from'));
        $dateTo = $this->normalizeLeadListDateInput($request->input('date_to'));
        if ($dateFrom && $dateTo && strcmp($dateFrom, $dateTo) > 0) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $leads = $query
            ->orderByRaw("CASE WHEN {$exportTable}.status = ? THEN 0 ELSE 1 END", ['active'])
            ->orderByDesc("{$exportTable}.created_at")
            ->orderByDesc("{$exportTable}.id")
            ->get();

        $fileName = 'leads_export_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($leads) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'Date',
                'Name',
                'Mobile',
                'Lead Type',
                'Lead Source',
                'Lead Response',
                'Company',
                'City',
                'Next Follow Up',
                'Tele-caller',
                'Sales Consultant',
                'Status',
            ]);

            foreach ($leads as $lead) {
                $teleCaller = optional($lead->teleCallers->first())->name;
                $salesConsultant = optional($lead->salesConsultants->first())->name;
                $statusLabel = match ($lead->status ?? 'active') {
                    'closed' => 'Closed',
                    'active' => 'Active',
                    default => 'Draft',
                };

                fputcsv($handle, [
                    $lead->id,
                    optional($lead->created_at)->format('Y-m-d H:i:s'),
                    trim(($lead->first_name ?? '') . ' ' . ($lead->last_name ?? '')),
                    $lead->mobile_no,
                    optional($lead->leadType)->name,
                    optional($lead->leadSource)->campaign,
                    optional($lead->leadResponse)->response,
                    optional($lead->company)->name,
                    $lead->city,
                    optional($lead->next_follow_up)->format('Y-m-d'),
                    $teleCaller,
                    $salesConsultant,
                    $statusLabel,
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $authUser = Auth::user();
            $enquiryTypes = EnquiryType::all();
            $leadTypes = LeadType::all();
            $leadSources = LeadSource::all();
            $leadStatuses = LeadStatus::all();
            $segments = Segment::active()->get();
            $companies = $this->getScopedCompaniesForUser($authUser);
            $customerLocations = Location::where('type', 'customer')->get();
            $dealerLocations = DealerLocation::all();
            $states = State::active()->orderBy('name')->get();
            $makes = Make::where('status', 'active')->orderBy('name')->get();
            $fuelTypes = FuelType::active()->orderBy('name')->get();
            $statusSources = StatusSource::with('leadStatus')->get();

            // Occupation types (static array)
            $occupationTypes = [
                'Salaried - Private',
                'Salaried - Government',
                'Self Employed',
                'Business Owner',
                'Professional',
                'Retired',
                'Student',
                'Homemaker',
                'Other'
            ];

            $statusSources = StatusSource::with('leadStatus')->get();

            return view('lead.create', compact(
                'enquiryTypes',
                'leadTypes',
                'leadSources',
                'leadStatuses',
                'segments',
                'companies',
                'customerLocations',
                'dealerLocations',
                'occupationTypes',
                'states',
                'makes',
                'fuelTypes',
                'statusSources'
            ));
        } catch (Exception $e) {
            Log::error('Error loading lead create form: ' . $e->getMessage());
            return redirect()->route('leads.index')->with('error', 'Unable to load create form. Please try again later.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                // Page 1 - Customer Details
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'mobile_no' => 'required|string|max:20',
                'email' => 'nullable|email|max:255',

                // Page 2 - Additional Details
                'gender' => 'nullable|in:male,female,other',
                'age_group' => 'nullable|in:under_30,30_to_40,above_40',
                'customer_type' => 'nullable|in:individual,corporate',
                'occupation_type' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'district' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'pincode' => 'nullable|string|max:10',
                'country' => 'nullable|string|max:255',

                // Page 3 - Enquiry Details
                'enquiry_type_id' => 'nullable|exists:enquiry_types,id',
                'lead_source_id' => 'nullable|exists:lead_sources,id',
                'lead_type_id' => 'nullable|exists:lead_types,id',
                'lead_status_id' => 'nullable|exists:lead_statuses,id',
                'segment_id' => 'nullable|exists:segments,id',
                'sub_segment_id' => 'nullable|exists:sub_segments,id',
                'vehicle_model_id' => 'nullable|exists:vehicle_models,id',
                'quantity' => 'nullable|integer|min:1',
                'variant_ids' => 'nullable|array',
                'variant_ids.*' => 'exists:variants,id',
                'fuel_type_ids' => 'nullable|array',
                'fuel_type_ids.*' => 'exists:fuel_types,id',
                'transmission_ids' => 'nullable|array',
                'transmission_ids.*' => 'exists:transmissions,id',
                'colour_ids' => 'nullable|array',
                'colour_ids.*' => 'exists:colours,id',

                // Page 4 - Purchase Details
                'purchase_type' => 'nullable|in:first_time_buy,exchange_buy,additional_buy,scrappage',
                'exchange_make_id' => 'nullable|exists:makes,id',
                'exchange_brand_model_id' => 'nullable|exists:brand_models,id',
                'exchange_fuel_type_id' => 'nullable|exists:fuel_types,id',
                'exchange_manufacture_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
                'exchange_kms' => 'nullable|string|max:255',
                'exchange_no_of_owners' => 'nullable|integer|min:0',
                'likely_purchase' => 'nullable|in:under_15_days,15_to_45_days,above_45_days,invalid_enquiry_closed',
                'lead_response_id' => 'nullable|exists:status_sources,id',
                'test_drive_at' => 'nullable|date|after_or_equal:now',
                'next_follow_up' => 'nullable|date|after_or_equal:today',

                // Page 5 - Assignment
                'company_id' => 'nullable|exists:companies,id',
                'location_id' => 'nullable|exists:dealer_locations,id',
                'additional_comments' => 'nullable|string',
                'status' => 'required|in:active,inactive,closed',
            ]);

            $isTestDriveScheduled = $this->isTestDriveScheduledResponse(
                isset($validated['lead_response_id']) ? (int) $validated['lead_response_id'] : null
            );

            if ($isTestDriveScheduled && empty($validated['test_drive_at'])) {
                throw ValidationException::withMessages([
                    'test_drive_at' => 'Test Drive Date & Time is required when Lead Response is Test Drive Scheduled.',
                ]);
            }

            if (!$isTestDriveScheduled) {
                $validated['test_drive_at'] = null;
            }

            $this->applyLeadResponseMasterMapping($validated);

            DB::beginTransaction();

            $validated['created_by'] = Auth::id();

            // Extract multi-select IDs before creating lead
            $variantIds = $validated['variant_ids'] ?? [];
            $fuelTypeIds = $validated['fuel_type_ids'] ?? [];
            $transmissionIds = $validated['transmission_ids'] ?? [];
            $colourIds = $validated['colour_ids'] ?? [];

            unset($validated['variant_ids'], $validated['fuel_type_ids'], $validated['transmission_ids'], $validated['colour_ids']);

            $lead = Lead::create($validated);

            // Auto-detect duplicate: same mobile_no + vehicle_model_id combination already exists
            if (!empty($validated['mobile_no']) && !empty($validated['vehicle_model_id'])) {
                $existingDuplicate = Lead::where('mobile_no', $validated['mobile_no'])
                    ->where('vehicle_model_id', $validated['vehicle_model_id'])
                    ->where('id', '!=', $lead->id)
                    ->exists();

                if ($existingDuplicate) {
                    $duplicateStatus = LeadStatus::where('name', 'duplicate')->first();
                    if ($duplicateStatus) {
                        $lead->update(['lead_status_id' => $duplicateStatus->id]);
                    }
                }
            }

            // Initial tele-caller assignment: creator as tele-caller (optional / best-effort).
            $creator = Auth::user();
            if ($creator && ($creator->hasRole('tele-caller') || $creator->hasRole('superadmin'))) {
                $lead->assignments()
                    ->where('assignment_type', 'tele_caller')
                    ->delete();

                $lead->assignments()->create([
                    'user_id'         => $creator->id,
                    'assignment_type' => 'tele_caller',
                    'assigned_by'     => $creator->id,
                ]);
            }

            // Sync multi-select relationships
            if (!empty($variantIds)) {
                $lead->variants()->sync($variantIds);
            }
            if (!empty($fuelTypeIds)) {
                $lead->fuelTypes()->sync($fuelTypeIds);
            }
            if (!empty($transmissionIds)) {
                $lead->transmissions()->sync($transmissionIds);
            }
            if (!empty($colourIds)) {
                $lead->colours()->sync($colourIds);
            }

            // Log activity
            $lead->activities()->create([
                'activity_type' => 'lead_created',
                'title' => 'Lead Created',
                'description' => 'Lead was created by ' . Auth::user()->name,
                'activity_date' => now(),
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('leads.index')
                ->with('success', 'Lead created successfully.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating lead: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Unable to create lead. Please check your input and try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Lead $lead)
    {
        $this->authorize('view', $lead);

        try {
            $lead->load([
                'leadType',
                'leadSource',
                'leadStatus',
                'leadResponse.leadStatus',
                'creator',
                'assignedUser',
                'teleCallers',
                'salesConsultants',
                'company',
                'location',
                'attachments.uploader',
                'calls.creator',
                'calls.responseMaster.leadStatus',
                'calls.likelyLeadType',
                'activities.creator',
                'notes.creator',
                'tasks.assignedUser',
                'tasks.creator',
                'reminders.creator',
                'tags.creator'
            ]);

            $companies = $this->getScopedCompaniesForUser(Auth::user());

            return view('lead.show', compact('lead', 'companies'));
        } catch (Exception $e) {
            Log::error('Error loading lead details: ' . $e->getMessage());
            return redirect()->route('leads.index')->with('error', 'Unable to load lead details. Please try again later.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lead $lead)
    {
        $this->authorize('update', $lead);

        try {
            $lead->load(['variants', 'fuelTypes', 'transmissions', 'colours', 'salesConsultants']);

            return view('lead.edit', $this->getEditFormData($lead));
        } catch (Exception $e) {
            Log::error('Error loading lead edit form: ' . $e->getMessage());
            return redirect()->route('leads.index')->with('error', 'Unable to load edit form. Please try again later.');
        }
    }

    private function getEditFormData(Lead $lead): array
    {
        return array_merge(
            ['lead' => $lead],
            $this->getLeadContextData($lead),
            $this->getLeadMasterData()
        );
    }

    private function getLeadContextData(Lead $lead): array
    {
        return [
            'subSegments'      => $lead->segment_id       ? SubSegment::where('segment_id', $lead->segment_id)->get()                        : collect(),
            'vehicleModels'    => $lead->sub_segment_id   ? VehicleModel::where('segment_id', $lead->segment_id)->active()->get()            : collect(),
            'variants'         => $lead->vehicle_model_id ? Variant::where('vehicle_model_id', $lead->vehicle_model_id)->active()->get()     : collect(),
            'locations'        => $lead->company_id       ? DealerLocation::where('company_id', $lead->company_id)->get()                    : collect(),
            'salesConsultants' => $lead->location_id      ? $this->getSalesConsultantsForLocation($lead->location_id)                        : collect(),
        ];
    }

    private function getLeadMasterData(): array
    {
        $authUser = Auth::user();

        return [
            'enquiryTypes'    => EnquiryType::all(),
            'leadTypes'       => LeadType::all(),
            'leadSources'     => LeadSource::all(),
            'leadStatuses'    => LeadStatus::all(),
            'segments'        => Segment::active()->get(),
            'companies'       => $this->getScopedCompaniesForUser($authUser),
            'states'          => State::active()->orderBy('name')->get(),
            'makes'           => Make::where('status', 'active')->orderBy('name')->get(),
            'fuelTypes'       => FuelType::active()->orderBy('name')->get(),
            'statusSources'   => StatusSource::with('leadStatus')->get(),
            'occupationTypes' => [
                'Salaried - Private',
                'Salaried - Government',
                'Self Employed',
                'Business Owner',
                'Professional',
                'Retired',
                'Student',
                'Homemaker',
                'Other',
            ],
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lead $lead)
    {
        $this->authorize('update', $lead);

        try {
            $validated = $request->validate([
                // Page 1 - Customer Details
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'mobile_no' => 'required|string|max:20',
                'email' => 'nullable|email|max:255',

                // Page 2 - Additional Details
                'gender' => 'nullable|in:male,female,other',
                'age_group' => 'nullable|in:under_30,30_to_40,above_40',
                'customer_type' => 'nullable|in:individual,corporate',
                'occupation_type' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'district' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'pincode' => 'nullable|string|max:10',
                'country' => 'nullable|string|max:255',

                // Page 3 - Enquiry Details
                'enquiry_type_id' => 'nullable|exists:enquiry_types,id',
                'lead_source_id' => 'nullable|exists:lead_sources,id',
                'lead_type_id' => 'nullable|exists:lead_types,id',
                'lead_status_id' => 'nullable|exists:lead_statuses,id',
                'segment_id' => 'nullable|exists:segments,id',
                'sub_segment_id' => 'nullable|exists:sub_segments,id',
                'vehicle_model_id' => 'nullable|exists:vehicle_models,id',
                'quantity' => 'nullable|integer|min:1',
                'variant_ids' => 'nullable|array',
                'variant_ids.*' => 'exists:variants,id',
                'fuel_type_ids' => 'nullable|array',
                'fuel_type_ids.*' => 'exists:fuel_types,id',
                'transmission_ids' => 'nullable|array',
                'transmission_ids.*' => 'exists:transmissions,id',
                'colour_ids' => 'nullable|array',
                'colour_ids.*' => 'exists:colours,id',

                // Page 4 - Purchase Details
                'purchase_type' => 'nullable|in:first_time_buy,exchange_buy,additional_buy,scrappage',
                'exchange_make_id' => 'nullable|exists:makes,id',
                'exchange_brand_model_id' => 'nullable|exists:brand_models,id',
                'exchange_fuel_type_id' => 'nullable|exists:fuel_types,id',
                'exchange_manufacture_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
                'exchange_kms' => 'nullable|string|max:255',
                'exchange_no_of_owners' => 'nullable|integer|min:0',
                'likely_purchase' => 'nullable|in:under_15_days,15_to_45_days,above_45_days,invalid_enquiry_closed',
                'lead_response_id' => 'nullable|exists:status_sources,id',
                'test_drive_at' => 'nullable|date|after_or_equal:now',
                'next_follow_up' => 'nullable|date|after_or_equal:today',

                // Page 5 - Assignment
                'company_id' => 'nullable|exists:companies,id',
                'location_id' => 'nullable|exists:dealer_locations,id',
                'additional_comments' => 'nullable|string',
                'status' => 'required|in:active,inactive,closed',
            ]);

            $isTestDriveScheduled = $this->isTestDriveScheduledResponse(
                isset($validated['lead_response_id']) ? (int) $validated['lead_response_id'] : null
            );

            if ($isTestDriveScheduled && empty($validated['test_drive_at'])) {
                throw ValidationException::withMessages([
                    'test_drive_at' => 'Test Drive Date & Time is required when Lead Response is Test Drive Scheduled.',
                ]);
            }

            if (!$isTestDriveScheduled) {
                $validated['test_drive_at'] = null;
            }

            $this->applyLeadResponseMasterMapping($validated);

            DB::beginTransaction();

            // Extract multi-select IDs before updating lead
            $variantIds = $validated['variant_ids'] ?? [];
            $fuelTypeIds = $validated['fuel_type_ids'] ?? [];
            $transmissionIds = $validated['transmission_ids'] ?? [];
            $colourIds = $validated['colour_ids'] ?? [];

            unset($validated['variant_ids'], $validated['fuel_type_ids'], $validated['transmission_ids'], $validated['colour_ids']);

            $lead->update($validated);

            // Sync multi-select relationships
            $lead->variants()->sync($variantIds);
            $lead->fuelTypes()->sync($fuelTypeIds);
            $lead->transmissions()->sync($transmissionIds);
            $lead->colours()->sync($colourIds);

            // Log activity
            $lead->activities()->create([
                'activity_type' => 'lead_updated',
                'title' => 'Lead Updated',
                'description' => 'Lead was updated by ' . Auth::user()->name,
                'activity_date' => now(),
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('leads.show', $lead)
                ->with('success', 'Lead updated successfully.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating lead: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Unable to update lead. Please check your input and try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lead $lead)
    {
        $this->authorize('delete', $lead);

        try {
            $lead->delete();

            return redirect()->route('leads.index')
                ->with('success', 'Lead deleted successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Check if it's a foreign key constraint error
            if ($e->getCode() == '23000') {
                Log::error('Foreign key constraint error deleting lead: ' . $e->getMessage());
                return redirect()->back()
                    ->with('error', 'Cannot delete this lead because it has related records. Please remove related data first.');
            }

            Log::error('Error deleting lead: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Unable to delete lead. Please try again later.');
        } catch (Exception $e) {
            Log::error('Error deleting lead: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Unable to delete lead. Please try again later.');
        }
    }

    /**
     * Assign lead to tele-caller and/or sales consultant.
     * Super admin can set both tele-caller and sales consultant.
     * Tele-caller (when assigned on the lead) can set only sales consultant.
     * Maintains assignment history via LeadActivity.
     */
    public function assign(Request $request, Lead $lead)
    {
        $this->authorize('assign', $lead);

        try {
            $user = Auth::user();
            $isSuperAdmin = $user->hasRole('superadmin');
            $currentCompanyId = $user->employee?->company_id;

            if ($isSuperAdmin) {
                // Super admin: can set company, location, tele-caller (required) and sales consultant (optional)
                $validated = $request->validate([
                    'company_id'          => 'required|exists:companies,id',
                    'location_id'         => 'required|exists:dealer_locations,id',
                    'tele_caller_id'      => 'required|exists:users,id',
                    'sales_consultant_id' => 'nullable|exists:users,id',
                ]);

                // Verify location belongs to company
                $location = DealerLocation::findOrFail($validated['location_id']);
                if ($location->company_id != $validated['company_id']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected location does not belong to the chosen company.',
                    ], 422);
                }

                // Validate tele-caller employee/location
                $teleCallerEmployee = Employee::where('user_id', $validated['tele_caller_id'])->first();
                if (!$teleCallerEmployee) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected tele-caller has no employee record.',
                    ], 422);
                }
                if ($teleCallerEmployee->is_left === 'yes') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected tele-caller has left and cannot be assigned.',
                    ], 422);
                }
                $teleLocations = is_array($teleCallerEmployee->dealer_location_id) ? $teleCallerEmployee->dealer_location_id : [];
                if (!in_array((int)$validated['location_id'], array_map('intval', $teleLocations))) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected tele-caller is not assigned to this location.',
                    ], 422);
                }

                $teleCallerUser = User::findOrFail($validated['tele_caller_id']);
                if (!$teleCallerUser->hasRole('tele-caller')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected user must have Tele-caller role to be assigned as tele-caller.',
                    ], 422);
                }

                $salesConsultantUser = null;
                if (!empty($validated['sales_consultant_id'])) {
                    $salesEmployee = Employee::where('user_id', $validated['sales_consultant_id'])->first();
                    if (!$salesEmployee) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Selected sales consultant has no employee record.',
                        ], 422);
                    }
                    if ($salesEmployee->is_left === 'yes') {
                        return response()->json([
                            'success' => false,
                            'message' => 'Selected sales consultant has left and cannot be assigned.',
                        ], 422);
                    }
                    $salesLocations = is_array($salesEmployee->dealer_location_id) ? $salesEmployee->dealer_location_id : [];
                    if (!in_array((int)$validated['location_id'], array_map('intval', $salesLocations))) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Selected sales consultant is not assigned to this location.',
                        ], 422);
                    }

                    $salesConsultantUser = User::findOrFail($validated['sales_consultant_id']);
                    if (!$salesConsultantUser->hasRole('sales-consultant')) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Selected user is not allowed as a sales consultant.',
                        ], 422);
                    }
                }

                // Update lead company & location
                $lead->update([
                    'company_id'  => $validated['company_id'],
                    'location_id' => $validated['location_id'],
                ]);

                // Sync assignments: one tele-caller (required)
                $lead->assignments()
                    ->where('assignment_type', 'tele_caller')
                    ->delete();

                $lead->assignments()->create([
                    'user_id'         => $teleCallerUser->id,
                    'assignment_type' => 'tele_caller',
                    'assigned_by'     => $user->id,
                ]);

                // Sync sales consultant (optional, single)
                $lead->assignments()
                    ->where('assignment_type', 'sales_consultant')
                    ->delete();

                if ($salesConsultantUser) {
                    $lead->assignments()->create([
                        'user_id'         => $salesConsultantUser->id,
                        'assignment_type' => 'sales_consultant',
                        'assigned_by'     => $user->id,
                    ]);
                }

                // Keep legacy assigned_to in sync (primary sales consultant if present, else tele-caller)
                $lead->assigned_to = $salesConsultantUser?->id ?? $teleCallerUser->id;
                $lead->save();

                $descParts = ['Tele-caller: ' . $teleCallerUser->name];
                if ($salesConsultantUser) {
                    $descParts[] = 'Sales consultant: ' . $salesConsultantUser->name;
                }

                $lead->activities()->create([
                    'activity_type' => 'lead_assigned',
                    'title'         => 'Lead assigned',
                    'description'   => implode(' | ', $descParts) . ' · by ' . $user->name,
                    'activity_date' => now(),
                    'created_by'    => $user->id,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Lead assigned successfully.',
                ]);
            }

            // Non-superadmin: must be assigned as tele-caller on this lead to assign sales consultant
            $isTeleCallerOnLead = $lead->assignments()
                ->where('assignment_type', 'tele_caller')
                ->where('user_id', $user->id)
                ->exists();

            if (!$isTeleCallerOnLead) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only assign sales consultants for leads where you are the tele-caller.',
                ], 403);
            }

            $validated = $request->validate([
                'company_id'          => 'required|exists:companies,id',
                'location_id'         => 'required|exists:dealer_locations,id',
                'sales_consultant_id' => 'required|exists:users,id',
            ]);

            if ($currentCompanyId && (int) $validated['company_id'] !== (int) $currentCompanyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only assign employees within your own company.',
                ], 403);
            }

            // Ensure company and location are not changed by tele-caller
            if ($lead->company_id && $lead->company_id != $validated['company_id']) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot change the company for this lead.',
                ], 422);
            }
            if ($lead->location_id && $lead->location_id != $validated['location_id']) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot change the location for this lead.',
                ], 422);
            }

            $location = DealerLocation::findOrFail($validated['location_id']);
            if ($lead->company_id && $location->company_id != $lead->company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected location does not belong to the chosen company.',
                ], 422);
            }

            // Validate sales consultant employee (non-superadmin)
            $assignedEmployee = Employee::where('user_id', $validated['sales_consultant_id'])->first();
            if (!$assignedEmployee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected sales consultant has no employee record.',
                ], 422);
            }
            if ($assignedEmployee->is_left === 'yes') {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected sales consultant has left and cannot be assigned.',
                ], 422);
            }

            // Tele-callers can assign to any sales consultant inside their own company.
            if ($currentCompanyId && (int) $assignedEmployee->company_id !== (int) $currentCompanyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only assign sales consultants within your own company.',
                ], 403);
            }

            $salesConsultantUser = User::findOrFail($validated['sales_consultant_id']);
            if (!$salesConsultantUser->hasRole('sales-consultant')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected user is not allowed as a sales consultant.',
                ], 422);
            }

            // Sync sales consultant assignment
            $lead->assignments()
                ->where('assignment_type', 'sales_consultant')
                ->delete();

            $lead->assignments()->create([
                'user_id'         => $salesConsultantUser->id,
                'assignment_type' => 'sales_consultant',
                'assigned_by'     => $user->id,
            ]);

            // Keep legacy assigned_to in sync
            $lead->assigned_to = $salesConsultantUser->id;
            $lead->save();

            $lead->activities()->create([
                'activity_type' => 'lead_assigned',
                'title'         => 'Sales consultant assigned',
                'description'   => 'Sales consultant set to ' . $salesConsultantUser->name . ' · by ' . $user->name,
                'activity_date' => now(),
                'created_by'    => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sales consultant assigned successfully.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Error assigning lead: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to assign lead. Please try again.',
            ], 500);
        }
    }

    /**
     * Bulk assign/reassign tele-caller for selected leads.
     * Mixed selection (assigned + unassigned) is not allowed.
     */
    public function bulkAssign(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user->hasRole('superadmin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only superadmin can bulk assign tele-callers.',
                ], 403);
            }

            $validated = $request->validate([
                'lead_ids' => 'required|array|min:1',
                'lead_ids.*' => 'integer|distinct|exists:leads,id',
                'tele_caller_id' => 'required|exists:users,id',
                'mode' => 'required|in:assign,reassign',
            ]);

            $teleCallerUser = User::findOrFail($validated['tele_caller_id']);
            if (!$teleCallerUser->hasRole('tele-caller')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected user must have Tele-caller role.',
                ], 422);
            }

            $teleCallerEmployee = Employee::where('user_id', $teleCallerUser->id)->first();
            if (!$teleCallerEmployee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected tele-caller has no employee record.',
                ], 422);
            }

            if ($teleCallerEmployee->is_left === 'yes' || $teleCallerEmployee->employee_status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected tele-caller is not active.',
                ], 422);
            }

            $leadIds = collect($validated['lead_ids'])->map(fn ($id) => (int) $id)->unique()->values();
            $leadStates = Lead::query()
                ->whereIn('id', $leadIds)
                ->withCount([
                    'assignments as tele_caller_assignment_count' => function ($query) {
                        $query->where('assignment_type', 'tele_caller');
                    }
                ])
                ->get();

            if ($leadStates->count() !== $leadIds->count()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some selected leads were not found.',
                ], 422);
            }

            $hasAssigned = $leadStates->contains(fn ($lead) => (int) $lead->tele_caller_assignment_count > 0);
            $hasUnassigned = $leadStates->contains(fn ($lead) => (int) $lead->tele_caller_assignment_count === 0);

            if ($hasAssigned && $hasUnassigned) {
                return response()->json([
                    'success' => false,
                    'message' => 'Select either only unassigned leads (assign) or only assigned leads (reassign).',
                ], 422);
            }

            if ($validated['mode'] === 'assign' && $hasAssigned) {
                return response()->json([
                    'success' => false,
                    'message' => 'Assignment mode supports only unassigned leads.',
                ], 422);
            }

            if ($validated['mode'] === 'reassign' && $hasUnassigned) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reassignment mode supports only already assigned leads.',
                ], 422);
            }

            DB::transaction(function () use ($leadStates, $teleCallerUser, $user, $validated) {
                foreach ($leadStates as $lead) {
                    $this->authorize('assign', $lead);

                    $lead->assignments()
                        ->where('assignment_type', 'tele_caller')
                        ->delete();

                    $lead->assignments()->create([
                        'user_id' => $teleCallerUser->id,
                        'assignment_type' => 'tele_caller',
                        'assigned_by' => $user->id,
                    ]);

                    $currentSalesConsultant = $lead->assignments()
                        ->where('assignment_type', 'sales_consultant')
                        ->latest('id')
                        ->first();

                    $lead->assigned_to = $currentSalesConsultant?->user_id ?? $teleCallerUser->id;
                    $lead->save();

                    $activityTitle = $validated['mode'] === 'assign'
                        ? 'Lead bulk assigned'
                        : 'Lead bulk reassigned';

                    $lead->activities()->create([
                        'activity_type' => 'lead_assigned',
                        'title' => $activityTitle,
                        'description' => 'Tele-caller: ' . $teleCallerUser->name . ' · by ' . $user->name . ' (bulk ' . $validated['mode'] . ')',
                        'activity_date' => now(),
                        'created_by' => $user->id,
                    ]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Tele-caller ' . ($validated['mode'] === 'assign' ? 'assigned' : 'reassigned') . ' successfully for selected leads.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Error in bulk lead assignment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to process bulk assignment. Please try again.',
            ], 500);
        }
    }

    /**
     * Get active tele-callers for bulk assignment.
     */
    public function getActiveTeleCallers()
    {
        try {
            $authUser = Auth::user();
            if (!$authUser->hasRole('superadmin')) {
                return response()->json([], 403);
            }

            $teleCallers = Employee::query()
                ->where('is_user', 'yes')
                ->where('employee_status', 'active')
                ->where('is_left', 'no')
                ->with('user.roles')
                ->get()
                ->filter(fn ($employee) => $employee->user && $employee->user->hasRole('tele-caller'))
                ->map(function ($employee) {
                    return [
                        'id' => $employee->user_id,
                        'name' => $employee->user->name,
                        'email' => $employee->user->email ?? '',
                    ];
                })
                ->sortBy('name')
                ->values();

            return response()->json($teleCallers);
        } catch (Exception $e) {
            Log::error('Error fetching active tele-callers: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to load tele-callers'], 500);
        }
    }

    // ============================================
    // AJAX Endpoints for Cascading Dropdowns
    // ============================================

    /**
     * Get sub-segments by segment ID
     */
    public function getSubSegments($segmentId)
    {
        try {
            $subSegments = SubSegment::where('segment_id', $segmentId)
                ->where('status', 'active')
                ->get(['id', 'subsegment_name as name']);

            return response()->json($subSegments);
        } catch (Exception $e) {
            Log::error('Error fetching sub-segments: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to load sub-segments'], 500);
        }
    }

    /**
     * Get vehicle models by sub-segment ID
     */
    public function getVehicleModels($subSegmentId)
    {
        try {
            // Find the sub-segment and get its parent segment_id
            $subSegment = SubSegment::findOrFail($subSegmentId);

            // Query vehicle models by the parent segment_id
            $vehicleModels = VehicleModel::where('segment_id', $subSegment->segment_id)
                ->active()
                ->get(['id', 'name']);

            return response()->json($vehicleModels);
        } catch (Exception $e) {
            Log::error('Error fetching vehicle models: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to load vehicle models'], 500);
        }
    }

    /**
     * Get variants by vehicle model ID
     */
    public function getVariants($vehicleModelId)
    {
        try {
            $variants = Variant::where('vehicle_model_id', $vehicleModelId)
                ->active()
                ->get(['id', 'name']);

            return response()->json($variants);
        } catch (Exception $e) {
            Log::error('Error fetching variants: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to load variants'], 500);
        }
    }

    /**
     * Get fuel types based on selected variants
     */
    public function getFuelTypes(Request $request)
    {
        try {
            $variantIds = $request->input('variant_ids', []);

            if (empty($variantIds)) {
                return response()->json([]);
            }

            // Get unique fuel types from selected variants
            $fuelTypes = FuelType::whereHas('variants', function($query) use ($variantIds) {
                $query->whereIn('variants.id', $variantIds);
            })->get(['id', 'name']);

            return response()->json($fuelTypes);
        } catch (Exception $e) {
            Log::error('Error fetching fuel types: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to load fuel types'], 500);
        }
    }

    /**
     * Get transmissions based on selected variants
     */
    public function getTransmissions(Request $request)
    {
        try {
            $variantIds = $request->input('variant_ids', []);

            if (empty($variantIds)) {
                return response()->json([]);
            }

            // Get unique transmissions from selected variants
            $transmissions = Transmission::whereHas('variants', function($query) use ($variantIds) {
                $query->whereIn('variants.id', $variantIds);
            })->get(['id', 'name']);

            return response()->json($transmissions);
        } catch (Exception $e) {
            Log::error('Error fetching transmissions: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to load transmissions'], 500);
        }
    }

    /**
     * Get colours based on selected variants
     */
    public function getColours(Request $request)
    {
        try {
            $variantIds = $request->input('variant_ids', []);

            if (empty($variantIds)) {
                return response()->json([]);
            }

            // Get unique colours from selected variants through vehicle models
            $colours = Colour::whereHas('vehicleModels.variants', function($query) use ($variantIds) {
                $query->whereIn('variants.id', $variantIds);
            })->get(['id', 'name']);

            return response()->json($colours);
        } catch (Exception $e) {
            Log::error('Error fetching colours: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to load colours'], 500);
        }
    }

    /**
     * Get dealer locations by company ID
     */
    public function getLocationsByCompany($companyId)
    {
        try {
            $authUser = Auth::user();
            if (!$authUser->hasRole('superadmin')) {
                $currentCompanyId = $authUser->employee?->company_id;
                if (!$currentCompanyId || (int) $currentCompanyId !== (int) $companyId) {
                    return response()->json([], 403);
                }
            }

            $locations = DealerLocation::where('company_id', $companyId)
                ->get(['id', 'name', 'state']);

            return response()->json($locations);
        } catch (Exception $e) {
            Log::error('Error fetching locations: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to load locations'], 500);
        }
    }

    /**
     * Get sales consultants by location ID.
     * Super admin sees all employees at location; others see only their reporting persons (direct reports).
     */
    public function getSalesConsultants($locationId)
    {
        try {
            $salesConsultants = $this->getSalesConsultantsForLocation($locationId);
            return response()->json($salesConsultants);
        } catch (Exception $e) {
            Log::error('Error fetching sales consultants: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to load sales consultants'], 500);
        }
    }

    /**
     * Get assignable tele-callers and sales consultants for a location.
     */
    public function getAssignableUsers($locationId)
    {
        try {
            $authUser = Auth::user();
            $location = DealerLocation::findOrFail($locationId);

            if (!$authUser->hasRole('superadmin')) {
                $currentCompanyId = $authUser->employee?->company_id;
                if (!$currentCompanyId || (int) $location->company_id !== (int) $currentCompanyId) {
                    return response()->json([
                        'tele_callers'      => [],
                        'sales_consultants' => [],
                    ]);
                }
            }

            // Base employee pool (role-specific scoping applied below)
            $query = Employee::query()
                ->where('is_user', 'yes')
                ->where('employee_status', 'active')
                ->where('is_left', 'no')
                ->with('user.roles');

            if (!$authUser->hasRole('superadmin')) {
                $currentEmployeeId = $authUser->employee?->id;
                $currentCompanyId = $authUser->employee?->company_id;
                if (!$currentCompanyId) {
                    return response()->json([
                        'tele_callers'      => [],
                        'sales_consultants' => [],
                    ]);
                }

                if ($authUser->hasRole('tele-caller')) {
                    // Tele-caller: any assignable users in own company (no location/reporting restriction)
                    $query->where('company_id', $currentCompanyId);
                } elseif ($currentEmployeeId !== null) {
                    // Other non-superadmin users: keep existing direct-report + location restrictions
                    $query->whereJsonContains('dealer_location_id', (int)$locationId);
                    $query->whereJsonContains('reporting_to', (int)$currentEmployeeId);
                    $query->where('company_id', $currentCompanyId);
                } else {
                    return response()->json([
                        'tele_callers'      => [],
                        'sales_consultants' => [],
                    ]);
                }
            } else {
                // Superadmin: keep location-based options
                $query->whereJsonContains('dealer_location_id', (int)$locationId);
            }

            $employees = $query->get()->filter(fn($e) => $e->user);

            $teleCallers = $employees->filter(function ($e) {
                return $e->user->hasRole('tele-caller');
            })->map(function ($e) {
                return (object)[
                    'id'    => $e->user_id,
                    'name'  => $e->user->name,
                    'email' => $e->user->email ?? '',
                ];
            })->sortBy('name')->values();

            $salesConsultants = $employees->filter(function ($e) {
                return $e->user->hasRole('sales-consultant');
            })->map(function ($e) {
                return (object)[
                    'id'    => $e->user_id,
                    'name'  => $e->user->name,
                    'email' => $e->user->email ?? '',
                ];
            })->sortBy('name')->values();

            return response()->json([
                'tele_callers'      => $teleCallers,
                'sales_consultants' => $salesConsultants,
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching assignable users: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to load assignable users'], 500);
        }
    }

    /**
     * Returns the IDs of the authenticated user plus all their direct reports.
     * Used for manager-level lead visibility scoping.
     */
    private function getAllowedUserIds(User $user): array
    {
        $allowedUserIds = [$user->id];
        $currentEmployeeId = $user->employee?->id;

        if ($currentEmployeeId !== null) {
            $directReportUserIds = Employee::whereJsonContains('reporting_to', (int) $currentEmployeeId)
                ->where('is_user', 'yes')
                ->pluck('user_id')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            $allowedUserIds = array_merge($allowedUserIds, $directReportUserIds);
        }

        return $allowedUserIds;
    }

    /**
     * Get sales consultants collection for a location (shared logic for edit page and AJAX).
     * Fetches from Employee model (filtered by location), returns user id/name/email for lead assignment.
     */
    private function getSalesConsultantsForLocation($locationId)
    {
        $authUser = Auth::user();
        $location = DealerLocation::find($locationId);
        if (!$location) {
            return collect();
        }

        // Base employee pool (role-specific scoping applied below)
        $query = Employee::query()
            ->where('is_user', 'yes')
            ->where('employee_status', 'active')
            ->where('is_left', 'no');

        if (!$authUser->hasRole('superadmin')) {
            $currentCompanyId = $authUser->employee?->company_id;
            if (!$currentCompanyId || (int) $location->company_id !== (int) $currentCompanyId) {
                return collect();
            }

            $currentEmployeeId = $authUser->employee?->id;
            if ($authUser->hasRole('tele-caller')) {
                // Tele-caller: any sales consultant in own company
                $query->where('company_id', $currentCompanyId);
            } elseif ($currentEmployeeId !== null) {
                // Other non-superadmin users: keep existing direct-report + location restrictions
                $query->whereJsonContains('dealer_location_id', (int)$locationId);
                $query->whereJsonContains('reporting_to', (int)$currentEmployeeId);
                $query->where('company_id', $currentCompanyId);
            } else {
                return collect(); // No employee record: non-superadmin sees no consultants
            }
        } else {
            // Superadmin: keep location-based options
            $query->whereJsonContains('dealer_location_id', (int)$locationId);
        }

        return $query->with('user')
            ->get()
            ->filter(fn($e) => $e->user)
            ->map(fn($e) => (object)[
                'id'    => $e->user_id,
                'name'  => $e->user->name,
                'email' => $e->user->email ?? '',
            ])
            ->sortBy('name')
            ->values();
    }

    /**
     * Get districts by state ID or name
     */
    public function getDistricts($stateIdentifier)
    {
        try {
            // Try to find state by ID first, then by name
            $state = State::where('id', $stateIdentifier)
                ->orWhere('name', $stateIdentifier)
                ->first();

            if (!$state) {
                return response()->json([]);
            }

            $districts = District::where('state_id', $state->id)
                ->active()
                ->orderBy('name')
                ->get(['id', 'name']);

            return response()->json($districts);
        } catch (Exception $e) {
            Log::error('Error fetching districts: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to load districts'], 500);
        }
    }

    /**
     * Get cities by district ID or name
     */
    public function getCities($districtIdentifier)
    {
        try {
            // Try to find district by ID first, then by name
            $district = District::where('id', $districtIdentifier)
                ->orWhere('name', $districtIdentifier)
                ->first();

            if (!$district) {
                return response()->json([]);
            }

            $cities = City::where('district_id', $district->id)
                ->active()
                ->orderBy('name')
                ->get(['id', 'name']);

            return response()->json($cities);
        } catch (Exception $e) {
            Log::error('Error fetching cities: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to load cities'], 500);
        }
    }

    /**
     * Get lead sources filtered by enquiry type
     */
    public function getLeadSourcesByEnquiryType($enquiryTypeId)
    {
        try {
            $leadSources = LeadSource::where('enquiry_type_id', $enquiryTypeId)
                ->orderBy('campaign')
                ->get(['id', 'campaign']);

            return response()->json([
                'success' => true,
                'lead_sources' => $leadSources,
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching lead sources by enquiry type: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'lead_sources' => [],
                'message' => 'Unable to load lead sources',
            ], 500);
        }
    }

    /**
     * Get brand models by make ID
     */
    public function getBrandModelsByMake($makeId)
    {
        try {
            $brandModels = BrandModel::where('make_id', $makeId)
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name']);

            return response()->json([
                'success' => true,
                'brand_models' => $brandModels,
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching brand models by make: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'brand_models' => [],
                'message' => 'Unable to load brand models',
            ], 500);
        }
    }

    /**
     * Get location details by pincode
     */
    public function getLocationByPincode($pincode)
    {
        try {
            // Remove any non-numeric characters
            $pincode = preg_replace('/[^0-9]/', '', $pincode);

            if (strlen($pincode) != 6) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid pincode. Please enter a 6-digit pincode.',
                ]);
            }

            // Try to fetch from external API (India Post API or similar)
            // For now, we'll use a simple approach - you can enhance this with actual API
            $locationData = $this->fetchLocationFromAPI($pincode);

            if ($locationData && isset($locationData['state'])) {
                // Find matching state, district, and city in our database
                $state = State::where('name', 'LIKE', '%' . $locationData['state'] . '%')
                    ->orWhere('name', 'LIKE', '%' . $locationData['stateName'] . '%')
                    ->first();

                if ($state) {
                    $district = null;
                    $city = null;

                    if (isset($locationData['district'])) {
                        $district = District::where('state_id', $state->id)
                            ->where('name', 'LIKE', '%' . $locationData['district'] . '%')
                            ->first();
                    }

                    if ($district && isset($locationData['city'])) {
                        $city = City::where('district_id', $district->id)
                            ->where('name', 'LIKE', '%' . $locationData['city'] . '%')
                            ->first();
                    }

                    return response()->json([
                        'success' => true,
                        'state' => $state ? ['id' => $state->id, 'name' => $state->name] : null,
                        'district' => $district ? ['id' => $district->id, 'name' => $district->name] : null,
                        'city' => $city ? ['id' => $city->id, 'name' => $city->name] : null,
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Location not found for this pincode. Please select manually.',
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching location by pincode: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch location. Please select manually.',
            ], 500);
        }
    }

    /**
     * Fetch location data from external API
     * Uses India Post API (api.postalpincode.in) - Free and reliable
     */
    private function fetchLocationFromAPI($pincode)
    {
        try {
            // Use India Post API - Free public API
            $response = Http::timeout(5)->get("https://api.postalpincode.in/pincode/{$pincode}");

            if ($response->status() === 200) {
                $data = $response->json();

                if (is_array($data) && isset($data[0]) && isset($data[0]['Status']) &&
                    $data[0]['Status'] === 'Success' &&
                    isset($data[0]['PostOffice']) && is_array($data[0]['PostOffice']) &&
                    count($data[0]['PostOffice']) > 0) {

                    // Get the first post office (usually the main one)
                    $postOffice = $data[0]['PostOffice'][0];

                    return [
                        'state' => $postOffice['State'] ?? null,
                        'district' => $postOffice['District'] ?? null,
                        'city' => $postOffice['Name'] ?? null,
                        'stateName' => $postOffice['State'] ?? null,
                    ];
                }
            }

            return null;
        } catch (Exception $e) {
            Log::error('Error fetching from pincode API: ' . $e->getMessage());
            return null;
        }
    }

    public function downloadImportTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="leads_import_sample.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['enquiry_type', 'lead_source', 'first_name', 'last_name', 'mobile_no', 'email', 'city', 'vehicle_model', 'lead_date']);
            fputcsv($handle, ['Digital', 'Car Dekho', 'John', 'Doe', '9876543210', 'john@example.com', 'Chennai', 'Scorpio-N', '2026-01-15']);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importLogs(Request $request)
    {
        $batchFilter = trim($request->string('batch')->toString());
        $statusFilter = trim($request->string('status')->toString());
        $dateFrom = trim($request->string('date_from')->toString());
        $dateTo = trim($request->string('date_to')->toString());

        $query = LeadImportBatch::query()
            ->with('creator')
            ->orderByDesc('created_at');

        if ($batchFilter !== '') {
            $query->where('batch_id', 'like', '%' . $batchFilter . '%');
        }
        if (in_array($statusFilter, ['queued', 'processing', 'completed', 'failed'], true)) {
            $query->where('status', $statusFilter);
        }
        if ($dateFrom !== '') {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo !== '') {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $batches = $query->paginate(20)->withQueryString();

        $batchIds = $batches->pluck('batch_id')->filter()->values();
        $logsByBatch = collect();
        if ($batchIds->isNotEmpty()) {
            $logsByBatch = CustomLog::query()
                ->where('context', 'lead_import')
                ->whereIn('data->batch_id', $batchIds)
                ->orderByDesc('created_at')
                ->get()
                ->groupBy(fn (CustomLog $log) => (string) data_get($log->data, 'batch_id'));
        }

        $legacyLogs = collect();
        if ($batchFilter === '' && $statusFilter === '' && $dateFrom === '' && $dateTo === '') {
            $legacyLogs = CustomLog::query()
                ->where('context', 'lead_import')
                ->where(function ($legacyQuery) {
                    $legacyQuery
                        ->whereNull('data->batch_id')
                        ->orWhere('data->batch_id', '');
                })
                ->orderByDesc('created_at')
                ->limit(25)
                ->get();
        }

        return view('lead.import-logs', [
            'batches' => $batches,
            'logsByBatch' => $logsByBatch,
            'legacyLogs' => $legacyLogs,
            'batchFilter' => $batchFilter,
            'statusFilter' => $statusFilter,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('file');

        if (!$file->isValid()) {
            return back()->with('error', 'Uploaded file is not valid.');
        }

        $storedPath = $file->store('imports/leads');

        if ($storedPath === false) {
            return back()->with('error', 'Unable to store uploaded file.');
        }

        $batchId = uniqid('lead_import_', true);

        LeadImportBatch::create([
            'batch_id' => $batchId,
            'file_path' => $storedPath,
            'created_by' => Auth::id(),
            'status' => 'queued',
        ]);

        \App\Jobs\ProcessLeadImport::dispatch($storedPath, $batchId, Auth::id())
            ->onConnection(config('queue.lead_import_connection'));

        $sync = config('queue.lead_import_connection') === 'sync';

        $success = $sync
            ? 'Import completed. New leads appear below. Use Import logs to see skipped rows, validation errors, and template issues.'
            : 'Import queued. Leads will be processed when the queue worker runs. Use Import logs to review results after processing.';

        return redirect()->route('leads.index')
            ->with('success', $success)
            ->with('import_batch_id', $batchId);
    }

    public function downloadUserDataTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="user_data_sample.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['first_name', 'last_name', 'mobile_no', 'vehicle_model']);
            fputcsv($handle, ['John', 'Doe', '9876543210', 'Scorpio-N']);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function userDataUpload(Request $request)
    {
        $request->validate([
            'file'             => 'required|file|mimes:csv,txt|max:10240',
            'status_source_id' => 'required|exists:status_sources,id',
        ]);

        $file = $request->file('file');

        if (!$file->isValid()) {
            return back()->with('error', 'Uploaded file is not valid.');
        }

        $storedPath = $file->store('imports/user-data');

        if ($storedPath === false) {
            return back()->with('error', 'Unable to store uploaded file.');
        }

        $batchId = uniqid('user_data_import_', true);

        \App\Jobs\ProcessUserDataImport::dispatch($storedPath, $batchId, (int) $request->input('status_source_id'), Auth::id());

        return redirect()->route('leads.index')
            ->with('success', 'User data upload started. Lead responses will be updated in the background.');
    }
}

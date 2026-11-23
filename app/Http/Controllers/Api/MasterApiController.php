<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\DealerLocation;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EnquiryType;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\StatusSource;
use App\Models\Variant;
use App\Models\VehicleModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterApiController extends Controller
{
    /**
     * List all active companies (organizations).
     */
    public function companies(): JsonResponse
    {
        $companies = Company::active()
            ->select(['id', 'name', 'code', 'city', 'state', 'status'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $companies,
        ]);
    }

    /**
     * List active branches (dealer locations) for a specific company.
     */
    public function branchesByCompany(string|int $companyId): JsonResponse
    {
        $company = Company::find($companyId);

        if (!$company || $company->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Company not found or inactive.',
            ], 404);
        }

        $branches = DealerLocation::active()
            ->where('company_id', $company->id)
            ->select(['id', 'company_id', 'name', 'code', 'city', 'state', 'status'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $branches,
        ]);
    }

    /**
     * List all active departments.
     */
    public function departments(): JsonResponse
    {
        $departments = Department::where('status', 'active')
            ->select(['id', 'department_name', 'status'])
            ->orderBy('department_name')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $departments,
        ]);
    }

    /**
     * List all active designations.
     */
    public function designations(): JsonResponse
    {
        $designations = Designation::where('status', 'active')
            ->select(['id', 'designation_name', 'status'])
            ->orderBy('designation_name')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $designations,
        ]);
    }

    /**
     * List active vehicle models with optional company/branch/segment filters.
     */
    public function vehicleModels(Request $request): JsonResponse
    {
        $query = VehicleModel::active()
            ->select(['id', 'name', 'segment_id', 'status'])
            ->with(['segment:id,name']);

        if ($request->filled('company_id')) {
            $companyId = (int) $request->company_id;
            $query->whereHas('companies', fn ($q) => $q->where('companies.id', $companyId));
        }

        if ($request->filled('dealer_location_id')) {
            $locationId = (int) $request->dealer_location_id;
            $query->whereHas('dealerLocations', fn ($q) => $q->where('dealer_locations.id', $locationId));
        }

        if ($request->filled('segment_id')) {
            $query->where('segment_id', (int) $request->segment_id);
        }

        $models = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data'    => $models,
        ]);
    }

    /**
     * List active variants for a specific vehicle model.
     */
    public function variantsByModel(string|int $modelId): JsonResponse
    {
        $model = VehicleModel::find($modelId);

        if (!$model || $model->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle model not found or inactive.',
            ], 404);
        }

        $variants = Variant::active()
            ->where('vehicle_model_id', $model->id)
            ->select(['id', 'vehicle_model_id', 'name', 'status'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $variants,
        ]);
    }

    /**
     * List all enquiry types.
     */
    public function enquiryTypes(): JsonResponse
    {
        $types = EnquiryType::select(['id', 'name'])->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data'    => $types,
        ]);
    }

    /**
     * List lead sources for a specific enquiry type.
     */
    public function sourcesByType(string|int $typeId): JsonResponse
    {
        $type = EnquiryType::find($typeId);

        if (!$type) {
            return response()->json([
                'success' => false,
                'message' => 'Enquiry type not found.',
            ], 404);
        }

        $sources = LeadSource::where('enquiry_type_id', $type->id)
            ->select(['id', 'enquiry_type_id', 'campaign'])
            ->orderBy('campaign')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $sources,
        ]);
    }

    /**
     * List all lead statuses.
     */
    public function leadStatuses(): JsonResponse
    {
        $statuses = LeadStatus::select(['id', 'name'])->orderBy('id')->get();

        return response()->json([
            'success' => true,
            'data'    => $statuses,
        ]);
    }

    /**
     * List status responses for a specific lead status.
     */
    public function responsesByStatus(string|int $statusId): JsonResponse
    {
        $status = LeadStatus::find($statusId);

        if (!$status) {
            return response()->json([
                'success' => false,
                'message' => 'Lead status not found.',
            ], 404);
        }

        $responses = StatusSource::where('lead_status_id', $status->id)
            ->select(['id', 'lead_status_id', 'response'])
            ->orderBy('response')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $responses,
        ]);
    }

    /**
     * List active employees with search and filtering (read-only, sanitized).
     * Passwords, tokens, and sensitive auth data are strictly excluded.
     */
    public function employees(Request $request): JsonResponse
    {
        $query = Employee::where('employee_status', 'active')
            ->where('is_left', 'no')
            ->select([
                'id',
                'emp_code',
                'mile_id',
                'employee_name',
                'company_id',
                'designation_id',
                'dealer_location_id',
                'mobile_number',
                'email',
                'employee_status',
                'date_of_joining',
            ])
            ->with([
                'company:id,name,code',
                'designation:id,designation_name',
            ]);

        if ($request->filled('company_id')) {
            $query->where('company_id', (int) $request->company_id);
        }

        if ($request->filled('dealer_location_id')) {
            $query->whereJsonContains('dealer_location_id', (int) $request->dealer_location_id);
        }

        if ($request->filled('designation_id')) {
            $query->where('designation_id', (int) $request->designation_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('employee_name', 'like', "%{$search}%")
                    ->orWhere('emp_code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }

        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);
        $employees = $query->orderBy('employee_name')->paginate($perPage);

        return response()->json([
            'success'    => true,
            'data'       => $employees->items(),
            'pagination' => [
                'current_page' => $employees->currentPage(),
                'per_page'     => $employees->perPage(),
                'total'        => $employees->total(),
                'last_page'    => $employees->lastPage(),
            ],
        ]);
    }
}

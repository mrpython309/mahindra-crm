<?php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\DealerLocation;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Segment;
use App\Models\SubSegment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    private function getCurrentCompanyId(): ?int
    {
        $user = Auth::user();
        if (!$user || $user->hasRole('superadmin')) {
            return null;
        }
        return $user->employee?->company_id ? (int) $user->employee->company_id : null;
    }

    /**
     * Rules for up to three optional report-to slots (employee IDs).
     */
    private function reportToPersonFieldRules(): array
    {
        return [
            'report_to_person_1' => 'nullable|exists:employees,id',
            'report_to_person_2' => 'nullable|exists:employees,id',
            'report_to_person_3' => 'nullable|exists:employees,id',
        ];
    }

    /**
     * Ordered list of distinct employee IDs from report_to_person_* (max 3).
     *
     * @throws ValidationException
     */
    private function normalizedReportingToFromPersonFields(Request $request): array
    {
        $slots = [
            $request->input('report_to_person_1'),
            $request->input('report_to_person_2'),
            $request->input('report_to_person_3'),
        ];

        $ids = [];
        foreach ($slots as $v) {
            if ($v === null || $v === '') {
                continue;
            }
            $id = (int) $v;
            if (in_array($id, $ids, true)) {
                throw ValidationException::withMessages([
                    'report_to_person_1' => ['Each report-to person must be different.'],
                ]);
            }
            $ids[] = $id;
        }

        return $ids;
    }

    private function getScopedCompanies()
    {
        $companyId = $this->getCurrentCompanyId();
        $query = Company::where('status', 'active');

        if ($companyId !== null) {
            $query->where('id', $companyId);
        }

        return $query->get();
    }

    private function buildEmployeeQuery(Request $request)
    {
        $query = Employee::with([
            'designation',
            'company',
            'user',
        ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('employee_name', 'like', "%{$search}%")
                    ->orWhere('emp_code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $companyId = $this->getCurrentCompanyId();
        if ($companyId !== null) {
            $query->where('company_id', $companyId);
        }

        if ($request->filled('segment_id')) {
            $query->whereJsonContains('segment_id', (int) $request->segment_id);
        }

        if ($request->filled('designation_id')) {
            $query->where('designation_id', $request->designation_id);
        }

        if ($request->filled('employee_status')) {
            $query->where('employee_status', $request->employee_status);
        }

        return $query;
    }

    /**
     * Resolve multi-segment JSON (segment_id[]) into a comma-separated label string per employee.
     */
    private function hydrateSegmentLabels(LengthAwarePaginator $paginator): void
    {
        $items = $paginator->getCollection();
        $segmentIds = $items->flatMap(function (Employee $e) {
            return array_map('intval', (array) ($e->segment_id ?? []));
        })->unique()->values()->all();

        if ($segmentIds === []) {
            foreach ($items as $employee) {
                $employee->setAttribute('segment_labels', '-');
            }

            return;
        }

        $namesById = Segment::whereIn('id', $segmentIds)->pluck('name', 'id');

        foreach ($items as $employee) {
            $labels = collect($employee->segment_id ?? [])
                ->map(fn ($id) => $namesById[(int) $id] ?? null)
                ->filter()
                ->values();
            $employee->setAttribute('segment_labels', $labels->isEmpty() ? '-' : $labels->implode(', '));
        }
    }

    /**
     * Resolve reporting_to JSON (employee IDs) into a comma-separated name string per row (list UI).
     */
    private function hydrateReportsToLabels(LengthAwarePaginator $paginator): void
    {
        $items = $paginator->getCollection();
        $reportIds = $items->flatMap(function (Employee $e) {
            return array_map('intval', (array) ($e->reporting_to ?? []));
        })->unique()->values()->all();

        if ($reportIds === []) {
            foreach ($items as $employee) {
                $employee->setAttribute('reports_to_labels', '-');
            }

            return;
        }

        $namesById = Employee::whereIn('id', $reportIds)->pluck('employee_name', 'id');

        foreach ($items as $employee) {
            $labels = collect($employee->reporting_to ?? [])
                ->map(fn ($id) => $namesById[(int) $id] ?? null)
                ->filter()
                ->values();
            $employee->setAttribute('reports_to_labels', $labels->isEmpty() ? '-' : $labels->implode(', '));
        }
    }

    public function index()
    {
        $companies    = $this->getScopedCompanies();
        $segments     = Segment::where('status', 'active')->get();
        $designations = Designation::where('status', 'active')->get();

        return view('employee.index', compact('companies', 'segments', 'designations'));
    }

    /**
     * Get paginated employee data for report/search (AJAX).
     */
    public function getData(Request $request)
    {
        $query = $this->buildEmployeeQuery($request);

        $employees = $query->orderBy('employee_name')->paginate(25)->withPath(route('employee.index'));
        $this->hydrateSegmentLabels($employees);
        $this->hydrateReportsToLabels($employees);

        return response()->json([
            'success'   => true,
            'html'      => view('employee.partials.table-rows', compact('employees'))->render(),
            'pagination' => view('employee.partials.pagination', compact('employees'))->render(),
        ]);
    }

    /**
     * Prefix cell so Excel opens Emp Code / MILE ID as text (avoids number formatting).
     */
    private function csvExcelTextCell(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return "\t" . $value;
    }

    public function export(Request $request)
    {
        $employees = $this->buildEmployeeQuery($request)
            ->with(['designation', 'company'])
            ->orderBy('employee_name')
            ->get();

        $allSegmentIds = $employees
            ->flatMap(fn (Employee $e) => array_map('intval', (array) ($e->segment_id ?? [])))
            ->unique()
            ->values()
            ->all();
        $segmentNamesById = $allSegmentIds === []
            ? collect()
            : Segment::whereIn('id', $allSegmentIds)->pluck('name', 'id');

        $allDeptIds = $employees
            ->flatMap(fn (Employee $e) => array_map('intval', (array) ($e->department_id ?? [])))
            ->unique()
            ->values()
            ->all();
        $deptNamesById = $allDeptIds === []
            ? collect()
            : Department::whereIn('id', $allDeptIds)->pluck('department_name', 'id');

        $allSubsegIds = $employees
            ->flatMap(fn (Employee $e) => array_map('intval', (array) ($e->subsegment_id ?? [])))
            ->unique()
            ->values()
            ->all();
        $subsegNamesById = $allSubsegIds === []
            ? collect()
            : SubSegment::whereIn('id', $allSubsegIds)->pluck('subsegment_name', 'id');

        $allLocIds = $employees
            ->flatMap(fn (Employee $e) => array_map('intval', (array) ($e->dealer_location_id ?? [])))
            ->unique()
            ->values()
            ->all();
        $locNamesById = $allLocIds === []
            ? collect()
            : DealerLocation::whereIn('id', $allLocIds)->pluck('name', 'id');

        $allReportIds = $employees
            ->flatMap(fn (Employee $e) => array_map('intval', (array) ($e->reporting_to ?? [])))
            ->unique()
            ->values()
            ->all();
        $reportNamesById = $allReportIds === []
            ? collect()
            : Employee::whereIn('id', $allReportIds)->pluck('employee_name', 'id');

        $computedReportMax = $employees->isEmpty()
            ? 0
            : (int) $employees->map(fn (Employee $e) => count((array) ($e->reporting_to ?? [])))->max();
        // Match sample sheet: at least 3 “Reporting to” columns; expand if any row has more managers.
        $maxReportSlots = max(3, $computedReportMax);

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="employees_' . now()->format('Ymd_His') . '.csv"',
        ];

        return response()->stream(function () use (
            $employees,
            $segmentNamesById,
            $deptNamesById,
            $subsegNamesById,
            $locNamesById,
            $reportNamesById,
            $maxReportSlots
        ) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");

            $headerRow = [
                'Emp Code',
                'MILE ID',
                'Company',
                'Location',
                'Department',
                'Segment',
                'Sub Segment',
                'Employee Name',
                'Designation',
                'Date of Joining',
                'Mobile Number',
                'Email',
                'Status',
                'Left Date',
                'Is User?',
            ];
            for ($i = 1; $i <= $maxReportSlots; $i++) {
                $headerRow[] = 'Reporting to ' . $i;
            }
            fputcsv($output, $headerRow);

            foreach ($employees as $employee) {
                $deptLine = collect($employee->department_id ?? [])
                    ->map(fn ($id) => $deptNamesById[(int) $id] ?? null)
                    ->filter()
                    ->implode(', ');

                $segmentLine = collect($employee->segment_id ?? [])
                    ->map(fn ($id) => $segmentNamesById[(int) $id] ?? null)
                    ->filter()
                    ->implode(', ');

                $subsegLine = collect($employee->subsegment_id ?? [])
                    ->map(fn ($id) => $subsegNamesById[(int) $id] ?? null)
                    ->filter()
                    ->implode(', ');

                $locLine = collect($employee->dealer_location_id ?? [])
                    ->map(fn ($id) => $locNamesById[(int) $id] ?? null)
                    ->filter()
                    ->implode(', ');

                $isUserLabel = match (strtolower((string) ($employee->is_user ?? ''))) {
                    'yes' => 'Yes',
                    'no'  => 'No',
                    default => '',
                };

                $row = [
                    $this->csvExcelTextCell($employee->emp_code),
                    $this->csvExcelTextCell($employee->mile_id),
                    optional($employee->company)->name ?? '',
                    $locLine,
                    $deptLine,
                    $segmentLine,
                    $subsegLine,
                    $employee->employee_name,
                    optional($employee->designation)->designation_name ?? '',
                    $employee->date_of_joining ? $employee->date_of_joining->format('d-m-Y') : '',
                    $this->csvExcelTextCell((string) $employee->mobile_number),
                    $employee->email,
                    $employee->employee_status ?? '',
                    $employee->left_date ? $employee->left_date->format('d-m-Y') : '',
                    $isUserLabel,
                ];

                $orderedReportIds = array_values(array_map('intval', (array) ($employee->reporting_to ?? [])));
                for ($j = 0; $j < $maxReportSlots; $j++) {
                    $rid = $orderedReportIds[$j] ?? null;
                    $row[] = $rid !== null ? (string) ($reportNamesById[$rid] ?? '') : '';
                }

                fputcsv($output, $row);
            }

            fclose($output);
        }, 200, $headers);
    }

    public function create()
    {
        $departments  = Department::where('status', 'active')->get();
        $designations = Designation::where('status', 'active')->get();
        $segments     = Segment::where('status', 'active')->get();
        $roles        = Role::where('id', '!=', 1)->get();
        $companies    = $this->getScopedCompanies();

        return view(
            'employee.create',
            compact('departments', 'designations', 'segments', 'roles', 'companies')
        );
    }

    public function storeOld(Request $request)
    {
        // --------------------------------------------------
        // 1. BASE VALIDATION (always required)
        // --------------------------------------------------
        $rules = [
            'emp_code'         => 'required|string|max:50|unique:users,emp_code',
            'mile_id'          => 'required|string|max:50',
            'employee_name'    => 'required|string|max:255',
            'date_of_joining'  => 'required|date',
            'mobile_number'    => 'required|digits:10',
            'email'            => 'required|email|unique:users,email',
            'designation_id'   => 'required|exists:designations,id',

            'reporting_status' => 'required|boolean',

            'is_user'          => 'nullable|in:yes,no',
            'role_id'          => 'nullable|exists:roles,id',
            'password'         => 'nullable|min:8',
            'employee_status'      => 'required|in:active,inactive'
        ];

        // --------------------------------------------------
        // 2. IF reporting_status = OFF (0) → FULL VALIDATION
        // --------------------------------------------------
        if ($request->reporting_status == 0) {

            $rules = array_merge($rules, [

                'company_id'           => 'required|exists:companies,id',
                'segment_id'           => 'required|exists:segments,id',

                'department_id'        => 'required|array',
                'department_id.*'      => 'exists:departments,id',

                'dealer_location_id'   => 'required|array',
                'dealer_location_id.*' => 'exists:dealer_locations,id',

                'subsegment_id'        => 'required|array',
                'subsegment_id.*'      => 'exists:sub_segments,id',
            ], $this->reportToPersonFieldRules());
        }


        $validated = $request->validate($rules);
        $currentCompanyId = $this->getCurrentCompanyId();
        if ($currentCompanyId !== null && isset($validated['company_id']) && (int) $validated['company_id'] !== $currentCompanyId) {
            return back()->withErrors(['company_id' => 'You can only select your own company.'])->withInput();
        }

        $reportingTo = ($request->reporting_status == 0)
            ? $this->normalizedReportingToFromPersonFields($request)
            : null;

        $user = User::create([
            'emp_code'           => $validated['emp_code'],
            'mile_id'            => $validated['mile_id'],
            'name'               => $validated['employee_name'],
            'email'              => $validated['email'],
            'designation_id'     => $validated['designation_id'],
            'date_of_joining'    => $validated['date_of_joining'],
            'mobile_number'      => $validated['mobile_number'],

            'reporting_status'   => $request->boolean('reporting_status'),
            'employee_status'    => $validated['employee_status'] ?? null,

            'company_id'         => $validated['company_id'] ?? null,
            'segment_id'         => $validated['segment_id'] ?? null,

            // JSON / array columns
            'department_id'      => $validated['department_id'] ?? null,
            'dealer_location_id' => $validated['dealer_location_id'] ?? null,
            'subsegment_id'      => $validated['subsegment_id'] ?? null,
            'reporting_to'       => $reportingTo,

            'is_user'            => $request->is_user === 'yes' ? 'yes' : 'no',

            'password'           => ($request->is_user === 'yes' && $request->password)
                ? Hash::make($request->password)
                : null,
        ]);


        if ($request->is_user === 'yes' && $request->role_id) {
            $role = Role::find($request->role_id);

            if ($role) {
                $user->syncRoles([$role->name]);
                $user->update(['role_id' => $request->role_id]);
            }
        }

        return redirect()
            ->route('employee.index')
            ->with('success', 'Employee created successfully');
    }

    public function store(Request $request)
    {
        // --------------------------------------------------
        // 1. BASE VALIDATION (always required)
        // --------------------------------------------------
        $rules = [
            'emp_code'        => [
                'required',
                'string',
                'max:50',
                'unique:employees,emp_code',
            ],
            'mile_id'         => 'required|string|max:50|unique:employees,mile_id',
            'employee_name'   => 'required|string|max:255',
            'date_of_joining' => 'required|date|before_or_equal:today',
            'mobile_number'   => 'required|digits:10|unique:employees,mobile_number',
            'email'           => [
                'required',
                'email',
                'unique:employees,email',
                'unique:users,email',
            ],
            'designation_id'  => 'required|exists:designations,id',
            'reporting_status' => 'nullable|boolean',
            'is_user'         => 'nullable|in:yes,no',
            'role_id'         => 'nullable|exists:roles,id',
            'password'        => 'nullable|min:8',
            'employee_status' => 'required|in:active,inactive',
        ];

        // --------------------------------------------------
        // 2. IF reporting_status = OFF (0) → FULL VALIDATION
        // --------------------------------------------------
        $reportingStatus = $request->boolean('reporting_status');

        if (! $reportingStatus) {
            $rules = array_merge($rules, [
                'company_id'           => 'required|exists:companies,id',
                'segment_id'           => 'required|array',
                'segment_id.*'         => 'exists:segments,id',
                'department_id'        => 'required|array',
                'department_id.*'      => 'exists:departments,id',
                'dealer_location_id'   => 'required|array',
                'dealer_location_id.*' => 'exists:dealer_locations,id',
                'subsegment_id'        => 'required|array',
                'subsegment_id.*'      => 'exists:sub_segments,id',
            ], $this->reportToPersonFieldRules());
        }

        // If employee is a user, role and password are required
        if ($request->is_user === 'yes') {
            $rules['role_id'] = 'required|exists:roles,id';
            $rules['password'] = 'required|min:8';
        }

        $validated = $request->validate($rules);

        $reportingTo = $reportingStatus ? [] : $this->normalizedReportingToFromPersonFields($request);

        DB::transaction(function () use ($validated, $request, $reportingTo) {
            // Create Employee (primary record)
            $employee = Employee::create([
                'emp_code'           => $validated['emp_code'],
                'mile_id'            => $validated['mile_id'],
                'employee_name'      => $validated['employee_name'],
                'email'              => $validated['email'],
                'designation_id'     => $validated['designation_id'],
                'date_of_joining'    => $validated['date_of_joining'],
                'mobile_number'      => $validated['mobile_number'],
                'employee_status'    => $validated['employee_status'],
                'company_id'         => $validated['company_id'] ?? 0,
                'segment_id'         => $validated['segment_id'] ?? null,
                'department_id'      => $validated['department_id'] ?? [],
                'dealer_location_id' => $validated['dealer_location_id'] ?? [],
                'subsegment_id'      => $validated['subsegment_id'] ?? [],
                'reporting_to'       => $reportingTo,
                'is_user'            => $request->is_user === 'yes' ? 'yes' : 'no',
                'is_left'            => 'no',
                'left_date'          => null,
                'role_id'            => $request->is_user === 'yes' ? $request->role_id : null,
            ]);

            // Optionally create linked User (login account)
            if ($request->is_user === 'yes') {
                $user = User::create([
                    'name'             => $validated['employee_name'],
                    'email'            => $validated['email'],
                    'password'         => Hash::make($validated['password']),
                    'is_user'          => 'yes',
                    'role_id'          => $request->role_id,
                    'reporting_status' => $request->boolean('reporting_status'),
                ]);

                if ($request->role_id) {
                    $role = Role::find($request->role_id);

                    if ($role) {
                        $user->syncRoles([$role->name]);
                    }
                }

                $employee->update([
                    'user_id' => $user->id,
                ]);
            }
        });

        return redirect()
            ->route('employee.index')
            ->with('success', 'Employee created successfully');
    }

    public function show(Employee $employee)
    {
        $employee->load([
            'company',
            'designation',
            'user',
            'user.roles',
        ]);

        $segmentIds      = $employee->segment_id ?? [];
        $departmentIds   = $employee->department_id ?? [];
        $locationIds     = $employee->dealer_location_id ?? [];
        $subsegmentIds   = $employee->subsegment_id ?? [];
        $reportingToIds = array_values(array_map('intval', (array) ($employee->reporting_to ?? [])));

        $segments        = Segment::whereIn('id', $segmentIds)->pluck('name')->toArray();
        $departments     = Department::whereIn('id', $departmentIds)->pluck('department_name')->toArray();
        $dealerLocations = DealerLocation::whereIn('id', $locationIds)->pluck('name')->toArray();
        $subsegments     = SubSegment::whereIn('id', $subsegmentIds)->pluck('subsegment_name')->toArray();

        $reportNamesById = $reportingToIds === []
            ? collect()
            : Employee::whereIn('id', $reportingToIds)->pluck('employee_name', 'id');
        $reportingToOrdered = [];
        foreach ($reportingToIds as $rid) {
            $name = $reportNamesById[$rid] ?? null;
            if ($name !== null) {
                $reportingToOrdered[] = ['id' => $rid, 'name' => $name];
            }
        }
        $reportingToNamesOnly = array_column($reportingToOrdered, 'name');

        return view('employee.show', compact(
            'employee',
            'segments',
            'departments',
            'dealerLocations',
            'subsegments',
            'reportingToOrdered',
            'reportingToNamesOnly'
        ));
    }

    public function destroy(Employee $employee)
    {
        DB::transaction(function () use ($employee) {
            $user = $employee->user;
            $employee->delete();
            if ($user) {
                $user->delete();
            }
        });

        return redirect()
            ->route('employee.index')
            ->with('success', 'Employee deleted successfully.');
    }

    public function edit(Employee $employee)
    {
        $employee->load(['company', 'designation', 'user']);

        $departments  = Department::where('status', 'active')->get();
        $designations = Designation::where('status', 'active')->get();
        $segments     = Segment::where('status', 'active')->get();
        $roles        = Role::where('id', '!=', 1)->get();
        $companies    = $this->getScopedCompanies();

        return view(
            'employee.edit',
            compact('employee', 'departments', 'designations', 'segments', 'roles', 'companies')
        );
    }

    public function updateOld(Request $request, $id)
    {
        $user = User::findOrFail($id);


        $rules = [
            'emp_code'         => 'required|string|max:50|unique:users,emp_code,' . $user->id,
            'mile_id'          => 'required|string|max:50',
            'employee_name'    => 'required|string|max:255',
            'date_of_joining'  => 'required|date',
            'mobile_number'    => 'required|digits:10',
            'email'            => 'required|email|unique:users,email,' . $user->id,
            'designation_id'   => 'required|exists:designations,id',

            'reporting_status' => 'required|boolean',

            'is_user'          => 'nullable|in:yes,no',
            'role_id'          => 'nullable|exists:roles,id',
            'password'         => 'nullable|min:8',
            'employee_status'      => 'required|in:active,inactive'
        ];



        if ($request->reporting_status == 0) {

            $rules = array_merge($rules, [

                'company_id'           => 'required|exists:companies,id',
                'segment_id'           => 'required|exists:segments,id',

                'department_id'        => 'required|array',
                'department_id.*'      => 'exists:departments,id',

                'dealer_location_id'   => 'required|array',
                'dealer_location_id.*' => 'exists:dealer_locations,id',

                'subsegment_id'        => 'required|array',
                'subsegment_id.*'      => 'exists:sub_segments,id',
            ], $this->reportToPersonFieldRules());
        }


        $validated = $request->validate($rules);
        $currentCompanyId = $this->getCurrentCompanyId();
        if ($currentCompanyId !== null && isset($validated['company_id']) && (int) $validated['company_id'] !== $currentCompanyId) {
            return back()->withErrors(['company_id' => 'You can only select your own company.'])->withInput();
        }

        $reportingTo = ($request->reporting_status == 0)
            ? $this->normalizedReportingToFromPersonFields($request)
            : null;

        $user->update([
            'emp_code'           => $validated['emp_code'],
            'mile_id'            => $validated['mile_id'],
            'name'               => $validated['employee_name'],
            'email'              => $validated['email'],
            'designation_id'     => $validated['designation_id'],
            'date_of_joining'    => $validated['date_of_joining'],
            'mobile_number'      => $validated['mobile_number'],

            'reporting_status'   => $request->boolean('reporting_status'),
            'employee_status'    => $validated['employee_status'] ?? null,

            'company_id'         => $validated['company_id'] ?? null,
            'segment_id'         => $validated['segment_id'] ?? null,

            // JSON / array fields
            'department_id'      => $validated['department_id'] ?? null,
            'dealer_location_id' => $validated['dealer_location_id'] ?? null,
            'subsegment_id'      => $validated['subsegment_id'] ?? null,
            'reporting_to'       => $reportingTo,

            'is_user'            => $request->is_user === 'yes' ? 'yes' : 'no',

            'password'           => ($request->is_user === 'yes' && $request->password)
                ? Hash::make($request->password)
                : $user->password,
        ]);


        if ($request->is_user === 'yes' && $request->role_id) {

            $role = Role::find($request->role_id);

            if ($role) {
                $user->syncRoles([$role->name]);
                $user->update(['role_id' => $request->role_id]);
            }

        } else {
            // remove roles if not a user
            $user->roles()->detach();
            $user->update(['role_id' => null]);
        }


        return redirect()
            ->route('employee.index')
            ->with('success', 'Employee updated successfully');
    }

    public function update(Request $request, Employee $employee)
    {
        $user = $employee->user;

        $rules = [
            'emp_code'        => [
                'required',
                'string',
                'max:50',
                Rule::unique('employees', 'emp_code')->ignore($employee->id),
            ],
            'mile_id'         => [
                'required',
                'string',
                'max:50',
                Rule::unique('employees', 'mile_id')->ignore($employee->id),
            ],
            'employee_name'   => 'required|string|max:255',
            'date_of_joining' => 'required|date|before_or_equal:today',
            'mobile_number'   => [
                'required',
                'digits:10',
                Rule::unique('employees', 'mobile_number')->ignore($employee->id),
            ],
            'email'           => [
                'required',
                'email',
                Rule::unique('employees', 'email')->ignore($employee->id),
                Rule::unique('users', 'email')->ignore(optional($user)->id),
            ],
            'designation_id'  => 'required|exists:designations,id',
            'reporting_status' => 'nullable|boolean',
            'is_user'         => 'nullable|in:yes,no',
            'role_id'         => 'nullable|exists:roles,id',
            'password'        => 'nullable|min:8',
            'employee_status' => 'required|in:active,inactive',
            'is_left'         => 'nullable|in:yes,no',
            'left_date'       => 'nullable|date',
        ];

        $reportingStatus = $request->boolean('reporting_status');

        if (! $reportingStatus) {
            $rules = array_merge($rules, [
                'company_id'           => 'required|exists:companies,id',
                'segment_id'           => 'required|array',
                'segment_id.*'         => 'exists:segments,id',
                'department_id'        => 'required|array',
                'department_id.*'      => 'exists:departments,id',
                'dealer_location_id'   => 'required|array',
                'dealer_location_id.*' => 'exists:dealer_locations,id',
                'subsegment_id'        => 'required|array',
                'subsegment_id.*'      => 'exists:sub_segments,id',
            ], $this->reportToPersonFieldRules());
        }

        if ($request->is_user === 'yes') {
            $rules['role_id'] = 'required|exists:roles,id';
            // Require password only when creating a new user account; when editing, empty = don't change password
            if (! $user) {
                $rules['password'] = 'required|min:8';
            }
        }

        $validated = $request->validate($rules);

        $reportingTo = $reportingStatus ? [] : $this->normalizedReportingToFromPersonFields($request);

        DB::transaction(function () use ($validated, $request, $employee, $user, $reportingTo) {
            $isUserFlag = $request->is_user === 'yes' ? 'yes' : 'no';
            $isLeftFlag = $request->is_left ?? $employee->is_left;

            // Update employee
            $employee->update([
                'emp_code'           => $validated['emp_code'],
                'mile_id'            => $validated['mile_id'],
                'employee_name'      => $validated['employee_name'],
                'email'              => $validated['email'],
                'designation_id'     => $validated['designation_id'],
                'date_of_joining'    => $validated['date_of_joining'],
                'mobile_number'      => $validated['mobile_number'],
                'employee_status'    => $validated['employee_status'],
                'company_id'         => $validated['company_id'] ?? 0,
                'segment_id'         => $validated['segment_id'] ?? null,
                'department_id'      => $validated['department_id'] ?? [],
                'dealer_location_id' => $validated['dealer_location_id'] ?? [],
                'subsegment_id'      => $validated['subsegment_id'] ?? [],
                'reporting_to'       => $reportingTo,
                'is_user'            => $isUserFlag,
                'is_left'            => $isLeftFlag,
                'left_date'          => $isLeftFlag === 'yes'
                    ? ($validated['left_date'] ?? $employee->left_date)
                    : null,
                'role_id'            => $isUserFlag === 'yes' ? $request->role_id : null,
            ]);

            // Handle user account based on is_user flag
            if ($isUserFlag === 'yes') {
                if ($user) {
                    $user->update([
                        'name'             => $validated['employee_name'],
                        'email'            => $validated['email'],
                        'password'         => $request->password
                            ? Hash::make($request->password)
                            : $user->password,
                        'is_user'          => 'yes',
                        'role_id'          => $request->role_id,
                        'reporting_status' => $request->boolean('reporting_status'),
                    ]);
                } else {
                    $user = User::create([
                        'name'             => $validated['employee_name'],
                        'email'            => $validated['email'],
                        'password'         => $request->password
                            ? Hash::make($request->password)
                            : null,
                        'is_user'          => 'yes',
                        'role_id'          => $request->role_id,
                        'reporting_status' => $request->boolean('reporting_status'),
                    ]);

                    $employee->update([
                        'user_id' => $user->id,
                    ]);
                }

                if ($request->role_id) {
                    $role = Role::find($request->role_id);

                    if ($role) {
                        $user->syncRoles([$role->name]);
                    }
                }
            } else {
                if ($user) {
                    $user->roles()->detach();
                    $user->update([
                        'is_user' => 'no',
                        'role_id' => null,
                    ]);

                    $employee->update([
                        'user_id' => null,
                    ]);
                }
            }
        });

        return redirect()
            ->route('employee.index')
            ->with('success', 'Employee updated successfully');
    }

    public function getBySegment($segmentId)
    {
        return SubSegment::where('segment_id', $segmentId)
            ->where('status', 'active')
            ->get();
    }

    public function byLocations($company_id)
    {
        $currentCompanyId = $this->getCurrentCompanyId();
        if ($currentCompanyId !== null && (int) $company_id !== $currentCompanyId) {
            return response()->json([]);
        }

        return DealerLocation::where('company_id', $company_id)
            ->where('status', 'active')
            ->get();
    }

    /**
     * Get employees by company (for Report To dropdown). Optional search by name/email/emp_code.
     * Pass exclude_employee to skip current employee (e.g. on edit page).
     */
    public function employeesByCompany($company, Request $request)
    {
        $currentCompanyId = $this->getCurrentCompanyId();
        if ($currentCompanyId !== null && (int) $company !== $currentCompanyId) {
            return response()->json([]);
        }

        $query = Employee::with('designation', 'company')
            ->where('company_id', $company)
            ->where('is_left', 'no')
            ->where('employee_status', 'active');

        if ($request->filled('exclude_employee')) {
            $query->where('id', '!=', $request->exclude_employee);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('employee_name', 'like', "%{$search}%")
                    ->orWhere('emp_code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $employees = $query->orderBy('employee_name')->get();

        if ($request->filled('include_report_employee_ids')) {
            $extraIds = array_values(array_unique(array_filter(array_map(
                'intval',
                explode(',', (string) $request->input('include_report_employee_ids'))
            ))));
            $haveIds = $employees->pluck('id')->all();
            $missing = array_values(array_diff($extraIds, $haveIds));
            if ($missing !== []) {
                $extras = Employee::with('designation', 'company')
                    ->whereIn('id', $missing)
                    ->get();
                $employees = $employees->concat($extras)->unique('id')->sortBy('employee_name')->values();
            }
        }

        return response()->json($employees);
    }

    public function byMultipleCompanies(Request $request)
    {
        $companyIds = $request->company_ids ?? [];
        $currentCompanyId = $this->getCurrentCompanyId();
        if ($currentCompanyId !== null) {
            $companyIds = array_values(array_filter((array) $companyIds, fn($id) => (int) $id === $currentCompanyId));
        }

        $companies = Company::with('dealerLocations')
            ->whereIn('id', $companyIds)
            ->get();

        return response()->json($companies);
    }

    public function hierarchy()
    {
        if (!Auth::user() || !Auth::user()->hasRole('superadmin')) {
            abort(403, 'Employee hierarchy is available only for super admin.');
        }

        $employees = Employee::with(['designation', 'company'])
            ->where('is_left', 'no')
            ->orderBy('employee_name')
            ->get();

        $employeesById = $employees->keyBy('id');
        $childrenByManager = [];

        foreach ($employees as $employee) {
            $managerIds = is_array($employee->reporting_to) ? $employee->reporting_to : [];
            if (empty($managerIds)) {
                continue;
            }

            foreach ($managerIds as $managerId) {
                $managerId = (int) $managerId;
                $childrenByManager[$managerId] = $childrenByManager[$managerId] ?? [];
                $childrenByManager[$managerId][] = $employee->id;
            }
        }

        $hasManagers = [];
        foreach ($childrenByManager as $managerId => $childIds) {
            foreach ($childIds as $childId) {
                $hasManagers[(int) $childId] = true;
            }
        }

        $rootIds = $employees
            ->filter(function ($employee) use ($hasManagers) {
                return empty($hasManagers[(int) $employee->id]);
            })
            ->pluck('id')
            ->values()
            ->toArray();

        return view('employee.hierarchy', compact('rootIds', 'employeesById', 'childrenByManager'));
    }

}

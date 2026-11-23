<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\LeadType;
use App\Models\Company;
use App\Models\DealerLocation;
use App\Models\Segment;
use App\Models\VehicleModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class ReportController extends Controller
{
    private function getCurrentCompanyId(): ?int
    {
        $authUser = Auth::user();
        if (!$authUser || $authUser->hasRole('superadmin')) {
            return null;
        }
        return $authUser->employee?->company_id ? (int) $authUser->employee->company_id : null;
    }

    private function getScopedCompanies()
    {
        $companyId = $this->getCurrentCompanyId();
        $query = Company::active();
        if ($companyId !== null) {
            $query->where('id', $companyId);
        }
        return $query->get();
    }

    private function applyCompanyScope($query, Request $request, string $column = 'company_id')
    {
        $companyId = $this->getCurrentCompanyId();
        if ($companyId !== null) {
            $query->where($column, $companyId);
            return $query;
        }

        if ($request->filled('company_id')) {
            $query->where($column, $request->company_id);
        }
        return $query;
    }

    /**
     * Apply role-based scoping to a Lead query (mirrors LeadController logic).
     */
    private function scopeLeadQuery($query)
    {
        $authUser = Auth::user();

        if ($authUser->hasRole('superadmin')) {
            return $query;
        }

        if ($authUser->hasRole('tele-caller')) {
            $query->whereHas('assignments', function ($q) use ($authUser) {
                $q->where('user_id', $authUser->id)
                  ->where('assignment_type', 'tele_caller');
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

        return $query;
    }

    /**
     * Get allowed user IDs for a manager (self + direct reports).
     */
    private function getAllowedUserIds($manager)
    {
        $ids = [$manager->id];

        $employee = \App\Models\Employee::where('user_id', $manager->id)->first();
        if ($employee) {
            $subordinates = \App\Models\Employee::where('reporting_to', $employee->id)
                ->whereNotNull('user_id')
                ->pluck('user_id')
                ->toArray();
            $ids = array_merge($ids, $subordinates);
        }

        return $ids;
    }

    /**
     * Apply common date range filter.
     */
    private function applyDateFilter($query, Request $request, string $column = 'created_at')
    {
        if ($request->filled('date_from')) {
            $query->whereDate($column, '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate($column, '<=', $request->date_to);
        }
        return $query;
    }

    // =========================================================================
    // 1. Reports Index
    // =========================================================================

    public function index()
    {
        return view('reports.index');
    }

    // =========================================================================
    // 2. Lead Summary Report
    // =========================================================================

    public function leadSummary(Request $request)
    {
        try {
            $companies   = $this->getScopedCompanies();
            $locations   = DealerLocation::query();
            if (($companyId = $this->getCurrentCompanyId()) !== null) {
                $locations->where('company_id', $companyId);
            }
            $locations = $locations->get();
            $leadTypes   = LeadType::all();
            $leadSources = LeadSource::all();
            $leadStatuses = LeadStatus::all();

            $query = $this->scopeLeadQuery(Lead::query());
            $query = $this->applyDateFilter($query, $request);

            $query = $this->applyCompanyScope($query, $request);
            if ($request->filled('location_id')) {
                $query->where('location_id', $request->location_id);
            }
            if ($request->filled('lead_type_id')) {
                $query->where('lead_type_id', $request->lead_type_id);
            }
            if ($request->filled('lead_source_id')) {
                $query->where('lead_source_id', $request->lead_source_id);
            }

            $totalLeads = (clone $query)->count();

            // Leads by status
            $byStatus = (clone $query)
                ->select('lead_status_id', DB::raw('count(*) as total'))
                ->groupBy('lead_status_id')
                ->with('leadStatus')
                ->get()
                ->map(fn($r) => [
                    'label' => $r->leadStatus?->name ?? 'Unassigned',
                    'value' => $r->total,
                ]);

            // Leads by source
            $bySource = (clone $query)
                ->select('lead_source_id', DB::raw('count(*) as total'))
                ->groupBy('lead_source_id')
                ->with('leadSource')
                ->get()
                ->map(fn($r) => [
                    'label' => $r->leadSource?->campaign ?? 'Unknown',
                    'value' => $r->total,
                ]);

            // Leads trend (daily, last 30 days by default)
            $trendQuery = clone $query;
            $groupBy = $request->get('trend', 'daily');

            if ($groupBy === 'monthly') {
                $trend = $trendQuery
                    ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as period"), DB::raw('count(*) as total'))
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
            } elseif ($groupBy === 'weekly') {
                $trend = $trendQuery
                    ->select(DB::raw("DATE_FORMAT(created_at, '%x-W%v') as period"), DB::raw('count(*) as total'))
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
            } else {
                $trend = $trendQuery
                    ->select(DB::raw('DATE(created_at) as period'), DB::raw('count(*) as total'))
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
            }

            // Leads by type
            $byType = (clone $query)
                ->select('lead_type_id', DB::raw('count(*) as total'))
                ->groupBy('lead_type_id')
                ->with('leadType')
                ->get()
                ->map(fn($r) => [
                    'label' => $r->leadType?->name ?? 'Unknown',
                    'value' => $r->total,
                ]);

            return view('reports.lead-summary', compact(
                'companies', 'locations', 'leadTypes', 'leadSources', 'leadStatuses',
                'totalLeads', 'byStatus', 'bySource', 'trend', 'byType'
            ));
        } catch (Exception $e) {
            Log::error('Report error (leadSummary): ' . $e->getMessage());
            return redirect()->route('reports.index')->with('error', 'Unable to load report. Please try again.');
        }
    }

    // =========================================================================
    // 3. Follow-up Due Report
    // =========================================================================

    public function followUp(Request $request)
    {
        try {
            $companies = $this->getScopedCompanies();
            $users = User::query();
            if (($companyId = $this->getCurrentCompanyId()) !== null) {
                $users->whereHas('employee', fn($q) => $q->where('company_id', $companyId));
            }
            $users = $users->get();

            $period = $request->get('period', 'today');

            $query = $this->scopeLeadQuery(Lead::with(['assignedUser', 'leadStatus', 'vehicleModel', 'company']));
            $query->whereNotNull('next_follow_up');

            $query = $this->applyCompanyScope($query, $request);
            if ($request->filled('assigned_to')) {
                $query->where('assigned_to', $request->assigned_to);
            }

            $today = Carbon::today();

            $overdueCount  = (clone $query)->whereDate('next_follow_up', '<', $today)->count();
            $todayCount    = (clone $query)->whereDate('next_follow_up', $today)->count();
            $thisWeekCount = (clone $query)->whereBetween('next_follow_up', [$today->copy()->addDay(), $today->copy()->endOfWeek()])->count();

            // Apply period filter for table
            if ($period === 'overdue') {
                $query->whereDate('next_follow_up', '<', $today);
            } elseif ($period === 'week') {
                $query->whereBetween('next_follow_up', [$today->copy()->addDay(), $today->copy()->endOfWeek()]);
            } else {
                $query->whereDate('next_follow_up', $today);
            }

            $leads = $query->orderBy('next_follow_up')->paginate(25);

            $barData = [
                ['label' => 'Overdue', 'value' => $overdueCount],
                ['label' => 'Due Today', 'value' => $todayCount],
                ['label' => 'This Week', 'value' => $thisWeekCount],
            ];

            return view('reports.follow-up', compact(
                'companies', 'users', 'leads', 'barData',
                'overdueCount', 'todayCount', 'thisWeekCount', 'period'
            ));
        } catch (Exception $e) {
            Log::error('Report error (followUp): ' . $e->getMessage());
            return redirect()->route('reports.index')->with('error', 'Unable to load report. Please try again.');
        }
    }

    // =========================================================================
    // 4. Employee Performance Report
    // =========================================================================

    public function employeePerformance(Request $request)
    {
        try {
            $companies = $this->getScopedCompanies();
            $locations = DealerLocation::query();
            if (($companyId = $this->getCurrentCompanyId()) !== null) {
                $locations->where('company_id', $companyId);
            }
            $locations = $locations->get();

            $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->toDateString());
            $dateTo   = $request->get('date_to', Carbon::today()->toDateString());
            $roleFilter = $request->get('role', 'all');

            // Leads assigned per user
            $assignmentsQuery = DB::table('lead_assignments')
                ->join('users', 'lead_assignments.user_id', '=', 'users.id')
                ->join('leads', 'lead_assignments.lead_id', '=', 'leads.id')
                ->select(
                    'users.id as user_id',
                    'users.name as user_name',
                    'lead_assignments.assignment_type',
                    DB::raw('count(distinct lead_assignments.lead_id) as leads_count')
                )
                ->whereBetween('leads.created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                ->groupBy('users.id', 'users.name', 'lead_assignments.assignment_type');

            $assignmentsQuery = $this->applyCompanyScope($assignmentsQuery, $request, 'leads.company_id');
            if ($request->filled('location_id')) {
                $assignmentsQuery->where('leads.location_id', $request->location_id);
            }

            $assignments = $assignmentsQuery->get();

            // Calls made per user (lead_calls uses created_by, not user_id)
            $callsQuery = DB::table('lead_calls')
                ->join('users', 'lead_calls.created_by', '=', 'users.id')
                ->join('leads', 'lead_calls.lead_id', '=', 'leads.id')
                ->select(
                    'users.id as user_id',
                    'users.name as user_name',
                    DB::raw('count(*) as calls_count')
                )
                ->whereBetween('lead_calls.created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                ->groupBy('users.id', 'users.name');

            $callsQuery = $this->applyCompanyScope($callsQuery, $request, 'leads.company_id');

            $callsData = $callsQuery->get()->keyBy('user_id');

            // Build performance data grouped by user
            $performanceMap = [];
            foreach ($assignments as $row) {
                $uid = $row->user_id;
                if (!isset($performanceMap[$uid])) {
                    $performanceMap[$uid] = [
                        'name'              => $row->user_name,
                        'tele_caller_leads' => 0,
                        'sales_leads'       => 0,
                        'calls_count'       => $callsData[$uid]->calls_count ?? 0,
                    ];
                }
                if ($row->assignment_type === 'tele_caller') {
                    $performanceMap[$uid]['tele_caller_leads'] += $row->leads_count;
                } else {
                    $performanceMap[$uid]['sales_leads'] += $row->leads_count;
                }
            }

            // Filter by role
            if ($roleFilter === 'tele-caller') {
                $performanceMap = array_filter($performanceMap, fn($r) => $r['tele_caller_leads'] > 0);
            } elseif ($roleFilter === 'sales-consultant') {
                $performanceMap = array_filter($performanceMap, fn($r) => $r['sales_leads'] > 0);
            }

            $performanceData = array_values($performanceMap);

            return view('reports.employee-performance', compact(
                'companies', 'locations', 'performanceData', 'dateFrom', 'dateTo', 'roleFilter'
            ));
        } catch (Exception $e) {
            Log::error('Report error (employeePerformance): ' . $e->getMessage());
            return redirect()->route('reports.index')->with('error', 'Unable to load report. Please try again.');
        }
    }

    // =========================================================================
    // 5. Vehicle Model Interest Report
    // =========================================================================

    public function vehicleInterest(Request $request)
    {
        try {
            $segments    = Segment::active()->get();
            $vehicleModels = VehicleModel::all();

            $query = $this->scopeLeadQuery(Lead::query());
            $query = $this->applyDateFilter($query, $request);

            if ($request->filled('segment_id')) {
                $query->where('segment_id', $request->segment_id);
            }
            if ($request->filled('sub_segment_id')) {
                $query->where('sub_segment_id', $request->sub_segment_id);
            }

            // Top 10 vehicle models
            $byModel = (clone $query)
                ->select('vehicle_model_id', DB::raw('count(*) as total'))
                ->whereNotNull('vehicle_model_id')
                ->groupBy('vehicle_model_id')
                ->orderByDesc('total')
                ->limit(10)
                ->with('vehicleModel')
                ->get()
                ->map(fn($r) => [
                    'label' => $r->vehicleModel?->name ?? 'Unknown',
                    'value' => $r->total,
                ]);

            // By segment
            $bySegment = (clone $query)
                ->select('segment_id', DB::raw('count(*) as total'))
                ->whereNotNull('segment_id')
                ->groupBy('segment_id')
                ->with('segment')
                ->get()
                ->map(fn($r) => [
                    'label' => $r->segment?->name ?? 'Unknown',
                    'value' => $r->total,
                ]);

            // Stacked: model vs fuel type (top 8 models)
            $topModelIds = (clone $query)
                ->select('vehicle_model_id', DB::raw('count(*) as total'))
                ->whereNotNull('vehicle_model_id')
                ->groupBy('vehicle_model_id')
                ->orderByDesc('total')
                ->limit(8)
                ->pluck('vehicle_model_id');

            $stackedRaw = DB::table('lead_fuel_type')
                ->join('leads', 'lead_fuel_type.lead_id', '=', 'leads.id')
                ->join('vehicle_models', 'leads.vehicle_model_id', '=', 'vehicle_models.id')
                ->join('fuel_types', 'lead_fuel_type.fuel_type_id', '=', 'fuel_types.id')
                ->whereIn('leads.vehicle_model_id', $topModelIds)
                ->select('vehicle_models.name as model', 'fuel_types.name as fuel', DB::raw('count(*) as total'))
                ->groupBy('vehicle_models.name', 'fuel_types.name')
                ->get();

            $stackedModels  = $stackedRaw->pluck('model')->unique()->values();
            $stackedFuels   = $stackedRaw->pluck('fuel')->unique()->values();
            $stackedSeries  = $stackedFuels->map(function ($fuel) use ($stackedRaw, $stackedModels) {
                return [
                    'name' => $fuel,
                    'data' => $stackedModels->map(function ($model) use ($stackedRaw, $fuel) {
                        return $stackedRaw->where('model', $model)->where('fuel', $fuel)->first()?->total ?? 0;
                    })->values(),
                ];
            });

            return view('reports.vehicle-interest', compact(
                'segments', 'vehicleModels', 'byModel', 'bySegment',
                'stackedModels', 'stackedSeries'
            ));
        } catch (Exception $e) {
            Log::error('Report error (vehicleInterest): ' . $e->getMessage());
            return redirect()->route('reports.index')->with('error', 'Unable to load report. Please try again.');
        }
    }

    // =========================================================================
    // 6. Lead Source Performance Report
    // =========================================================================

    public function leadSource(Request $request)
    {
        try {
            $companies   = $this->getScopedCompanies();
            $leadTypes   = LeadType::all();
            $leadSources = LeadSource::all();
            $leadStatuses = LeadStatus::all();

            $query = $this->scopeLeadQuery(Lead::query());
            $query = $this->applyDateFilter($query, $request);

            $query = $this->applyCompanyScope($query, $request);
            if ($request->filled('lead_type_id')) {
                $query->where('lead_type_id', $request->lead_type_id);
            }

            // Leads by source (donut)
            $bySource = (clone $query)
                ->select('lead_source_id', DB::raw('count(*) as total'))
                ->groupBy('lead_source_id')
                ->with('leadSource')
                ->get()
                ->map(fn($r) => [
                    'label' => $r->leadSource?->campaign ?? 'Unknown',
                    'value' => $r->total,
                ]);

            // Source vs status (stacked bar)
            $crossRaw = (clone $query)
                ->select('lead_source_id', 'lead_status_id', DB::raw('count(*) as total'))
                ->groupBy('lead_source_id', 'lead_status_id')
                ->with('leadSource', 'leadStatus')
                ->get();

            $sources  = $crossRaw->pluck('leadSource.campaign')->filter()->unique()->values();
            $statuses = $leadStatuses->pluck('name');

            $crossSeries = $statuses->map(function ($status) use ($crossRaw, $sources, $leadStatuses) {
                $statusId = $leadStatuses->firstWhere('name', $status)?->id;
                return [
                    'name' => $status,
                    'data' => $sources->map(function ($source) use ($crossRaw, $statusId) {
                        return $crossRaw
                            ->filter(fn($r) => $r->leadSource?->campaign === $source && $r->lead_status_id === $statusId)
                            ->sum('total');
                    })->values(),
                ];
            });

            return view('reports.lead-source', compact(
                'companies', 'leadTypes', 'leadSources', 'leadStatuses',
                'bySource', 'sources', 'crossSeries'
            ));
        } catch (Exception $e) {
            Log::error('Report error (leadSource): ' . $e->getMessage());
            return redirect()->route('reports.index')->with('error', 'Unable to load report. Please try again.');
        }
    }

    // =========================================================================
    // 7. Dealer Location-wise Report
    // =========================================================================

    public function locationWise(Request $request)
    {
        try {
            $companies    = $this->getScopedCompanies();
            $locations    = DealerLocation::with('company');
            if (($companyId = $this->getCurrentCompanyId()) !== null) {
                $locations->where('company_id', $companyId);
            }
            $locations = $locations->get();
            $leadStatuses = LeadStatus::all();

            $query = $this->scopeLeadQuery(Lead::query());
            $query = $this->applyDateFilter($query, $request);

            $query = $this->applyCompanyScope($query, $request);

            // Leads per location (bar)
            $byLocation = (clone $query)
                ->select('location_id', DB::raw('count(*) as total'))
                ->whereNotNull('location_id')
                ->groupBy('location_id')
                ->with('location')
                ->get()
                ->map(fn($r) => [
                    'label' => $r->location?->name ?? 'Unknown',
                    'value' => $r->total,
                ]);

            // Location vs status (stacked bar)
            $crossRaw = (clone $query)
                ->select('location_id', 'lead_status_id', DB::raw('count(*) as total'))
                ->whereNotNull('location_id')
                ->groupBy('location_id', 'lead_status_id')
                ->with('location', 'leadStatus')
                ->get();

            $locationNames = $crossRaw->pluck('location.name')->filter()->unique()->values();
            $statuses      = $leadStatuses->pluck('name');

            $stackedSeries = $statuses->map(function ($status) use ($crossRaw, $locationNames, $leadStatuses) {
                $statusId = $leadStatuses->firstWhere('name', $status)?->id;
                return [
                    'name' => $status,
                    'data' => $locationNames->map(function ($loc) use ($crossRaw, $statusId) {
                        return $crossRaw
                            ->filter(fn($r) => $r->location?->name === $loc && $r->lead_status_id === $statusId)
                            ->sum('total');
                    })->values(),
                ];
            });

            return view('reports.location-wise', compact(
                'companies', 'locations', 'leadStatuses',
                'byLocation', 'locationNames', 'stackedSeries'
            ));
        } catch (Exception $e) {
            Log::error('Report error (locationWise): ' . $e->getMessage());
            return redirect()->route('reports.index')->with('error', 'Unable to load report. Please try again.');
        }
    }

    // =========================================================================
    // 8. Likely Purchase Report
    // =========================================================================

    public function likelyPurchase(Request $request)
    {
        try {
            $companies = $this->getScopedCompanies();
            $users = User::query();
            if (($companyId = $this->getCurrentCompanyId()) !== null) {
                $users->whereHas('employee', fn($q) => $q->where('company_id', $companyId));
            }
            $users = $users->get();

            $query = $this->scopeLeadQuery(Lead::query());
            $query = $this->applyDateFilter($query, $request);
            $query->whereNotNull('likely_purchase');

            $query = $this->applyCompanyScope($query, $request);
            if ($request->filled('assigned_to')) {
                $query->where('assigned_to', $request->assigned_to);
            }

            $totalLeads = (clone $query)->count();

            // Donut: by likely_purchase
            $byLikely = (clone $query)
                ->select('likely_purchase', DB::raw('count(*) as total'))
                ->groupBy('likely_purchase')
                ->get()
                ->map(fn($r) => [
                    'label' => match ($r->likely_purchase) {
                        'under_15_days'  => 'Under 15 Days',
                        '15_to_45_days'  => '15 to 45 Days',
                        'above_45_days'  => 'Above 45 Days',
                        default          => ucfirst(str_replace('_', ' ', $r->likely_purchase)),
                    },
                    'value' => $r->total,
                ]);

            // Bar: likely_purchase by vehicle model
            $byModelRaw = (clone $query)
                ->select('vehicle_model_id', 'likely_purchase', DB::raw('count(*) as total'))
                ->whereNotNull('vehicle_model_id')
                ->groupBy('vehicle_model_id', 'likely_purchase')
                ->with('vehicleModel')
                ->get();

            $modelNames     = $byModelRaw->pluck('vehicleModel.name')->filter()->unique()->values();
            $likelyLabels   = ['under_15_days' => 'Under 15 Days', '15_to_45_days' => '15 to 45 Days', 'above_45_days' => 'Above 45 Days'];

            $modelSeries = collect($likelyLabels)->map(function ($label, $key) use ($byModelRaw, $modelNames) {
                return [
                    'name' => $label,
                    'data' => $modelNames->map(function ($model) use ($byModelRaw, $key) {
                        return $byModelRaw
                            ->filter(fn($r) => $r->vehicleModel?->name === $model && $r->likely_purchase === $key)
                            ->sum('total');
                    })->values(),
                ];
            })->values();

            return view('reports.likely-purchase', compact(
                'companies', 'users', 'totalLeads', 'byLikely', 'modelNames', 'modelSeries'
            ));
        } catch (Exception $e) {
            Log::error('Report error (likelyPurchase): ' . $e->getMessage());
            return redirect()->route('reports.index')->with('error', 'Unable to load report. Please try again.');
        }
    }
}

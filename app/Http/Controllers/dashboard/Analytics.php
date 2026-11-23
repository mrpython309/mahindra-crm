<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Analytics extends Controller
{
    public function index()
    {
        $currentYear = now()->year;
        $user = Auth::user();
        $scopedLeads = $this->getScopedLeadsQuery($user);

        // KPI Cards
        $totalLeads = (clone $scopedLeads)->count();
        $newToday = (clone $scopedLeads)->whereDate('created_at', today())->count();

        $bookedStatus = LeadStatus::where('name', 'like', '%book%')->first();
        if ($bookedStatus) {
            $bookedLeads = (clone $scopedLeads)->where('lead_status_id', $bookedStatus->id)->count();
        } else {
            $bookedResponse = \App\Models\StatusSource::where('response', 'like', '%book%')->first();
            $bookedLeads = $bookedResponse
                ? (clone $scopedLeads)->where('lead_response_id', $bookedResponse->id)->count()
                : 0;
        }

        $conversionRate = $totalLeads > 0
            ? round(($bookedLeads / $totalLeads) * 100, 1)
            : 0;

        // Leads by Month (current year)
        $monthlyRaw = (clone $scopedLeads)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', $currentYear)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $leadsMonthly = [
            'labels' => $monthNames,
            'data'   => array_map(fn($m) => (int) ($monthlyRaw[$m] ?? 0), range(1, 12)),
        ];

        // Leads by Status
        $statusGroups = (clone $scopedLeads)
            ->select('lead_status_id', DB::raw('COUNT(*) as count'))
            ->with('leadStatus')
            ->whereNotNull('lead_status_id')
            ->groupBy('lead_status_id')
            ->get();

        $leadsByStatus = [
            'labels' => $statusGroups->map(fn($r) => $r->leadStatus?->name ?? 'Unknown')->values()->toArray(),
            'data'   => $statusGroups->map(fn($r) => (int) $r->count)->values()->toArray(),
        ];

        // Leads by Source
        $sourceGroups = (clone $scopedLeads)
            ->select('lead_source_id', DB::raw('COUNT(*) as count'))
            ->with('leadSource')
            ->whereNotNull('lead_source_id')
            ->groupBy('lead_source_id')
            ->orderByDesc('count')
            ->limit(8)
            ->get();

        $leadsBySource = [
            'labels' => $sourceGroups->map(fn($r) => $r->leadSource?->campaign ?? 'Unknown')->values()->toArray(),
            'data'   => $sourceGroups->map(fn($r) => (int) $r->count)->values()->toArray(),
        ];

        // Leads by Segment
        $segmentGroups = (clone $scopedLeads)
            ->select('segment_id', DB::raw('COUNT(*) as count'))
            ->with('segment')
            ->whereNotNull('segment_id')
            ->groupBy('segment_id')
            ->get();

        $leadsBySegment = [
            'labels' => $segmentGroups->map(fn($r) => $r->segment?->name ?? 'Unknown')->values()->toArray(),
            'data'   => $segmentGroups->map(fn($r) => (int) $r->count)->values()->toArray(),
        ];

        // Top Vehicle Models
        $modelGroups = (clone $scopedLeads)
            ->select('vehicle_model_id', DB::raw('COUNT(*) as count'))
            ->with('vehicleModel')
            ->whereNotNull('vehicle_model_id')
            ->groupBy('vehicle_model_id')
            ->orderByDesc('count')
            ->limit(7)
            ->get();

        $topVehicleModels = [
            'labels' => $modelGroups->map(fn($r) => $r->vehicleModel?->name ?? 'Unknown')->values()->toArray(),
            'data'   => $modelGroups->map(fn($r) => (int) $r->count)->values()->toArray(),
        ];

        // Dashboard follow-up list (hidden for sales consultants)
        $showTodayFollowUps = $user && ! $user->hasRole('sales-consultant');
        $todayFollowUps = collect();
        if ($showTodayFollowUps) {
            $todayFollowUps = (clone $scopedLeads)->with([
                'leadStatus',
                'company',
                'location',
            ])->whereDate('next_follow_up', today())
                ->orderBy('next_follow_up')
                ->orderByDesc('id')
                ->limit(10)
                ->get();
        }

        return view('content.dashboard.dashboards-analytics', compact(
            'totalLeads',
            'newToday',
            'bookedLeads',
            'conversionRate',
            'leadsMonthly',
            'leadsByStatus',
            'leadsBySource',
            'leadsBySegment',
            'topVehicleModels',
            'todayFollowUps',
            'showTodayFollowUps'
        ));
    }

    private function getScopedLeadsQuery(?User $user)
    {
        $query = Lead::query();

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('superadmin')) {
            return $query;
        }

        if ($user->hasRole('tele-caller')) {
            return $query->whereHas('assignments', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->where('assignment_type', 'tele_caller');
            });
        }

        if ($user->hasRole('manager')) {
            $allowedUserIds = $this->getAllowedUserIds($user);

            return $query->whereHas('assignments', function ($q) use ($allowedUserIds) {
                $q->whereIn('user_id', $allowedUserIds);
            });
        }

        if ($user->hasRole('sales-consultant')) {
            return $query->whereHas('assignments', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        return $query->whereRaw('1 = 0');
    }

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
}

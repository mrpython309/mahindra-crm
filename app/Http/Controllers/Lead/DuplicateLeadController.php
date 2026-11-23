<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class DuplicateLeadController extends Controller
{
    public function index()
    {
        try {
            $authUser = Auth::user();

            $duplicateStatusId = LeadStatus::where('name', 'duplicate')->value('id');

            if (!$duplicateStatusId) {
                return view('lead.duplicate.index', ['usersWithStats' => collect()]);
            }

            // Find all users who have leads with "duplicate" status
            $query = Lead::select('assigned_to', DB::raw('COUNT(*) as total_leads'))
                ->where('lead_status_id', $duplicateStatusId)
                ->whereNotNull('assigned_to')
                ->groupBy('assigned_to');

            if (!$authUser->hasRole('superadmin')) {
                if ($authUser->hasRole('tele-caller') || $authUser->hasRole('sales-consultant')) {
                    $allowedUserIds = [$authUser->id];
                } elseif ($authUser->hasRole('manager')) {
                    $allowedUserIds = $this->getAllowedUserIds($authUser);
                } else {
                    $allowedUserIds = [];
                }
                $query->whereIn('assigned_to', $allowedUserIds);
            }

            $userStats = $query->get();

            $userIds = $userStats->pluck('assigned_to')->toArray();
            $users   = User::with('roles')->whereIn('id', $userIds)->get()->keyBy('id');

            // Also count distinct mobile+model groups per user
            $groupCounts = Lead::select('assigned_to', 'mobile_no', 'vehicle_model_id')
                ->where('lead_status_id', $duplicateStatusId)
                ->whereNotNull('assigned_to')
                ->when(!$authUser->hasRole('superadmin'), function ($q) use ($userIds) {
                    $q->whereIn('assigned_to', $userIds);
                })
                ->get()
                ->groupBy('assigned_to')
                ->map(fn($rows) => $rows->groupBy(fn($r) => $r->mobile_no . '|' . $r->vehicle_model_id)->count());

            $usersWithStats = $userStats->map(function ($stat) use ($users, $groupCounts) {
                $user = $users->get($stat->assigned_to);
                return [
                    'user_id'        => $stat->assigned_to,
                    'user'           => $user,
                    'duplicate_groups' => $groupCounts->get($stat->assigned_to, 0),
                    'total_leads'    => $stat->total_leads,
                ];
            })->filter(fn($item) => $item['user'] !== null)->values();

            return view('lead.duplicate.index', compact('usersWithStats'));
        } catch (Exception $e) {
            Log::error('Error loading duplicate leads index: ' . $e->getMessage());
            abort(500, 'Unable to load duplicate leads.');
        }
    }

    public function show(User $user)
    {
        try {
            $authUser = Auth::user();

            if (!$authUser->hasRole('superadmin')) {
                $allowedUserIds = [];
                if ($authUser->hasRole('tele-caller') || $authUser->hasRole('sales-consultant')) {
                    $allowedUserIds = [$authUser->id];
                } elseif ($authUser->hasRole('manager')) {
                    $allowedUserIds = $this->getAllowedUserIds($authUser);
                }
                abort_unless(in_array($user->id, $allowedUserIds), 403);
            }

            $duplicateStatusId = LeadStatus::where('name', 'duplicate')->value('id');

            $leads = Lead::where('assigned_to', $user->id)
                ->where('lead_status_id', $duplicateStatusId)
                ->with(['leadStatus', 'leadSource', 'leadType', 'creator', 'assignedUser', 'vehicleModel'])
                ->orderBy('mobile_no')
                ->orderBy('vehicle_model_id')
                ->orderBy('created_at', 'desc')
                ->get();

            // Group by mobile_no + vehicle_model_id combined key
            $groupedLeads = $leads->groupBy(fn($lead) => $lead->mobile_no . '|' . ($lead->vehicle_model_id ?? ''));

            $user->load('roles');

            return view('lead.duplicate.show', compact('user', 'groupedLeads'));
        } catch (Exception $e) {
            Log::error('Error loading duplicate leads for user ' . $user->id . ': ' . $e->getMessage());
            abort(500, 'Unable to load duplicate leads.');
        }
    }

    private function getAllowedUserIds(User $user): array
    {
        $allowedUserIds    = [$user->id];
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

        return array_unique($allowedUserIds);
    }
}

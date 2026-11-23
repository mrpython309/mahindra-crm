<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function view(User $user, Lead $lead): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $this->userCanSeeLead($user, $lead);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'tele-caller', 'manager', 'sales-consultant']);
    }

    public function update(User $user, Lead $lead): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $this->userCanSeeLead($user, $lead);
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $user->hasRole('superadmin');
    }

    public function assign(User $user, Lead $lead): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        // Tele-caller can assign a sales consultant only if they are the tele_caller on this lead
        if ($user->hasRole('tele-caller')) {
            return $lead->assignments()
                ->where('user_id', $user->id)
                ->where('assignment_type', 'tele_caller')
                ->exists();
        }

        // Manager can assign leads they can see
        if ($user->hasRole('manager')) {
            return $this->userCanSeeLead($user, $lead);
        }

        return false;
    }

    private function userCanSeeLead(User $user, Lead $lead): bool
    {
        if ($user->hasRole('tele-caller')) {
            return $lead->assignments()
                ->where('user_id', $user->id)
                ->where('assignment_type', 'tele_caller')
                ->exists();
        }

        if ($user->hasRole('manager')) {
            $allowedUserIds = $this->getAllowedUserIds($user);

            return $lead->assignments()
                ->whereIn('user_id', $allowedUserIds)
                ->exists();
        }

        if ($user->hasRole('sales-consultant')) {
            return $lead->assignments()
                ->where('user_id', $user->id)
                ->exists();
        }

        return false;
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

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeadStatus;
use App\Models\StatusSource;

class StatusSourceSeeder extends Seeder
{
    /**
     * Seed the lead response (status source) master based on Active / Closed tree.
     */
    public function run(): void
    {
        // Fetch core lead statuses seeded in LeadMasterSeeder
        $activeStatus = LeadStatus::where('name', 'active')->first();
        $closedStatus = LeadStatus::where('name', 'closed')->first();

        if (! $activeStatus || ! $closedStatus) {
            return;
        }

        // Active responses (green box in diagram)
        $activeResponses = [
            'Interested',
            'Positive Response',
            'TD Appointment taken',
            'TD Completed',
            'No Response/ No Reply',
        ];

        // Closed responses (red box in diagram)
        $closedResponses = [
            'Duplicate Inquiry',
            'Far from reach',
            'Forever No Response',
            'Just Inquiry',
            'Lost to other Brand or Co dealer',
            'Negative Response',
            'Booked',
            'Number Not Contactable',
        ];

        foreach ($activeResponses as $response) {
            StatusSource::updateOrCreate(
                [
                    'response' => $response,
                    'lead_status_id' => $activeStatus->id,
                ],
                []
            );
        }

        foreach ($closedResponses as $response) {
            StatusSource::updateOrCreate(
                [
                    'response' => $response,
                    'lead_status_id' => $closedStatus->id,
                ],
                []
            );
        }
    }
}


<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EnquiryType;
use App\Models\LeadType;
use App\Models\LeadSource;
use App\Models\LeadStatus;

class LeadMasterSeeder extends Seeder
{
    /**
     * Seed core lead master tables: enquiry types, lead types, lead sources, lead statuses.
     */
    public function run(): void
    {
        // Enquiry Types
        $enquiryTypes = [
            'Showroom Visit',
            'Walk-in',
            'Phone',
            'Website',
            'Campaign',
            'Corporate',
        ];

        $enquiryTypeMap = [];
        foreach ($enquiryTypes as $name) {
            $enquiryType = EnquiryType::updateOrCreate(
                ['name' => $name],
                ['name' => $name],
            );
            $enquiryTypeMap[$name] = $enquiryType->id;
        }

        // Lead Types (Buying window)
        $leadTypes = [
            ['name' => 'Hot',   'likely_purchase' => 'under_15_days'],
            ['name' => 'Warm',  'likely_purchase' => '15_to_45_days'],
            ['name' => 'Cold',  'likely_purchase' => 'above_45_days'],
            ['name' => 'Invalid Enquiry (Closed)', 'likely_purchase' => 'invalid_enquiry_closed'],
        ];

        foreach ($leadTypes as $type) {
            LeadType::updateOrCreate(
                ['name' => $type['name']],
                $type,
            );
        }

        // Lead Sources mapped to enquiry types
        $leadSources = [
            // Showroom / walk-in
            ['campaign' => 'Showroom Walk-in',        'enquiry_type' => 'Showroom Visit'],
            ['campaign' => 'Roadshow / Event',        'enquiry_type' => 'Campaign'],

            // Digital
            ['campaign' => 'Website Lead',            'enquiry_type' => 'Website'],
            ['campaign' => 'Google Ads',              'enquiry_type' => 'Campaign'],
            ['campaign' => 'Facebook / Instagram',    'enquiry_type' => 'Campaign'],

            // Phone / referrals
            ['campaign' => 'Inbound Call',            'enquiry_type' => 'Phone'],
            ['campaign' => 'Outbound Calling',        'enquiry_type' => 'Phone'],
            ['campaign' => 'Customer Referral',       'enquiry_type' => 'Walk-in'],

            // Corporate
            ['campaign' => 'Corporate / Fleet',       'enquiry_type' => 'Corporate'],
        ];

        foreach ($leadSources as $source) {
            $enquiryId = $enquiryTypeMap[$source['enquiry_type']] ?? null;
            if (!$enquiryId) {
                continue;
            }

            LeadSource::updateOrCreate(
                [
                    'campaign' => $source['campaign'],
                    'enquiry_type_id' => $enquiryId,
                ],
                [
                    'campaign' => $source['campaign'],
                    'enquiry_type_id' => $enquiryId,
                ],
            );
        }

        // Lead Statuses (pipeline stages)
        // Underlying enum only allows 'active' and 'closed' – we keep that,
        // and interpret them in UI/logic as "Open" vs "Closed".
        LeadStatus::updateOrCreate(
            ['name' => 'active'],
            ['name' => 'active'],
        );

        LeadStatus::updateOrCreate(
            ['name' => 'closed'],
            ['name' => 'closed'],
        );
    }
}


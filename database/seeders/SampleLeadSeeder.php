<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lead;
use App\Models\EnquiryType;
use App\Models\LeadType;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\Segment;
use App\Models\SubSegment;
use App\Models\VehicleModel;
use App\Models\Company;
use App\Models\DealerLocation;
use App\Models\User;
use App\Models\Variant;
use App\Models\FuelType;
use App\Models\Transmission;
use App\Models\Colour;
use App\Models\LeadAssignment;
use Carbon\Carbon;

class SampleLeadSeeder extends Seeder
{
    public function run(): void
    {
        $enquiryTypes = EnquiryType::all();
        $leadTypes = LeadType::all();
        $leadSources = LeadSource::all();
        $leadStatuses = LeadStatus::all();
        $segments = Segment::all();
        $subSegments = SubSegment::all();
        $vehicleModels = VehicleModel::all();
        $companies = Company::all();
        $locations = DealerLocation::all();
        $users = User::all();
        $variants = Variant::all();
        $fuelTypes = FuelType::all();
        $transmissions = Transmission::all();
        $colours = Colour::all();

        if ($enquiryTypes->isEmpty() || $leadStatuses->isEmpty()) {
            $this->command?->error("Please run database seeders first to populate metadata.");
            return;
        }

        $firstNames = ['Rahul', 'Amit', 'Priya', 'Suresh', 'Ramesh', 'Vikram', 'Sneha', 'Anjali', 'Karan', 'Deepak', 'Vijay', 'Sunita', 'Rajesh', 'Neelam', 'Sanjay', 'Geeta', 'Arun', 'Kavita', 'Manish', 'Pooja'];
        $lastNames = ['Sharma', 'Verma', 'Gupta', 'Patel', 'Singh', 'Kumar', 'Joshi', 'Mehta', 'Reddy', 'Nair', 'Chawla', 'Mishra', 'Yadav', 'Trivedi', 'Rao', 'Sen', 'Das', 'Roy', 'Jain', 'Bose'];
        
        $genders = ['male', 'female'];
        $ageGroups = ['under_30', '30_to_40', 'above_40'];
        $customerTypes = ['individual', 'corporate'];
        $occupations = ['salaried', 'self_employed', 'business', 'professional', 'retired', 'student', 'housewife'];
        $purchaseTypes = ['first_time_buy', 'exchange_buy', 'additional_buy', 'scrappage'];
        $likelyPurchases = ['under_15_days', '15_to_45_days', 'above_45_days'];

        $cities = [
            ['city' => 'Chennai', 'district' => 'Chennai', 'state' => 'Tamil Nadu', 'pincode' => '600001'],
            ['city' => 'Coimbatore', 'district' => 'Coimbatore', 'state' => 'Tamil Nadu', 'pincode' => '641001'],
            ['city' => 'Madurai', 'district' => 'Madurai', 'state' => 'Tamil Nadu', 'pincode' => '625001'],
            ['city' => 'Bangalore', 'district' => 'Bangalore Urban', 'state' => 'Karnataka', 'pincode' => '560001'],
            ['city' => 'Hyderabad', 'district' => 'Hyderabad', 'state' => 'Telangana', 'pincode' => '500001']
        ];

        $this->command?->info("Seeding 60 sample leads...");

        for ($i = 0; $i < 60; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $gender = $genders[array_rand($genders)];
            $ageGroup = $ageGroups[array_rand($ageGroups)];
            $customerType = $customerTypes[array_rand($customerTypes)];
            $occupation = $occupations[array_rand($occupations)];
            $purchaseType = $purchaseTypes[array_rand($purchaseTypes)];
            $likelyPurchase = $likelyPurchases[array_rand($likelyPurchases)];

            $cityData = $cities[array_rand($cities)];
            
            $enquiryType = $enquiryTypes->random();
            $leadType = $leadTypes->random();
            
            // Filter lead sources by enquiry type if possible
            $matchingSources = $leadSources->where('enquiry_type_id', $enquiryType->id);
            $leadSource = $matchingSources->isNotEmpty() ? $matchingSources->random() : $leadSources->random();
            
            $leadStatus = $leadStatuses->random();
            
            $segment = $segments->random();
            $matchingSubSegments = $subSegments->where('segment_id', $segment->id);
            $subSegment = $matchingSubSegments->isNotEmpty() ? $matchingSubSegments->random() : $subSegments->random();
            
            $matchingModels = $vehicleModels->where('sub_segment_id', $subSegment->id);
            $vehicleModel = $matchingModels->isNotEmpty() ? $matchingModels->random() : $vehicleModels->random();
            
            $company = $companies->random();
            $matchingLocations = $locations->where('company_id', $company->id);
            $location = $matchingLocations->isNotEmpty() ? $matchingLocations->random() : $locations->random();
            
            $user = $users->random();

            // Create lead created_at timestamp spread across Jan - Jul of 2026
            $month = rand(1, 7);
            $day = rand(1, 28);
            $createdAt = Carbon::create(2026, $month, $day, rand(9, 18), rand(0, 59), rand(0, 59));

            $lead = Lead::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'mobile_no' => '9' . str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT),
                'email' => strtolower($firstName . '.' . $lastName . rand(10, 99) . '@example.com'),
                'gender' => $gender,
                'age_group' => $ageGroup,
                'customer_type' => $customerType,
                'occupation_type' => $occupation,
                'city' => $cityData['city'],
                'district' => $cityData['district'],
                'state' => $cityData['state'],
                'pincode' => $cityData['pincode'],
                'country' => 'India',
                
                'enquiry_type_id' => $enquiryType->id,
                'lead_source_id' => $leadSource->id,
                'lead_type_id' => $leadType->id,
                'lead_status_id' => $leadStatus->id,
                'segment_id' => $segment->id,
                'sub_segment_id' => $subSegment->id,
                'vehicle_model_id' => $vehicleModel->id,
                'quantity' => rand(1, 3),
                
                'purchase_type' => $purchaseType,
                'likely_purchase' => $likelyPurchase,
                'next_follow_up' => $createdAt->copy()->addDays(rand(3, 10))->toDateString(),
                'test_drive_at' => rand(0, 1) ? $createdAt->copy()->addDays(rand(1, 5))->toDateTimeString() : null,
                
                'company_id' => $company->id,
                'location_id' => $location->id,
                'assigned_to' => $user->id,
                'additional_comments' => 'Generated sample lead for dashboard preview.',
                'status' => 'active',
                'created_by' => $user->id,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Attach relations
            if ($variants->isNotEmpty()) {
                $matchingVariants = $variants->where('vehicle_model_id', $vehicleModel->id);
                $selectedVariants = $matchingVariants->isNotEmpty() ? $matchingVariants->random(min(2, $matchingVariants->count())) : $variants->random(1);
                $lead->variants()->attach($selectedVariants->pluck('id')->toArray());
            }

            if ($fuelTypes->isNotEmpty()) {
                $lead->fuelTypes()->attach($fuelTypes->random(min(2, $fuelTypes->count()))->pluck('id')->toArray());
            }

            if ($transmissions->isNotEmpty()) {
                $lead->transmissions()->attach($transmissions->random(min(1, $transmissions->count()))->pluck('id')->toArray());
            }

            if ($colours->isNotEmpty()) {
                $lead->colours()->attach($colours->random(min(2, $colours->count()))->pluck('id')->toArray());
            }

            // Create assignments
            LeadAssignment::create([
                'lead_id' => $lead->id,
                'user_id' => $user->id,
                'assignment_type' => 'sales_consultant',
                'assigned_by' => 1,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        $this->command?->info("Sample leads seeded successfully!");
    }
}

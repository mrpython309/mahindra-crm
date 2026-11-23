<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        // Page 1 - Customer Details
        'first_name',
        'last_name',
        'mobile_no',
        'email',
        
        // Page 2 - Additional Details
        'gender',
        'age_group',
        'customer_type',
        'occupation_type',
        'city',
        'district',
        'state',
        'pincode',
        'country',
        
        // Page 3 - Enquiry Details
        'enquiry_type_id',
        'lead_source_id',
        'lead_type_id',
        'lead_status_id',
        'segment_id',
        'sub_segment_id',
        'vehicle_model_id',
        'quantity',
        
        // Page 4 - Purchase Details
        'purchase_type',
        'exchange_make_id',
        'exchange_brand_model_id',
        'exchange_fuel_type_id',
        'exchange_manufacture_year',
        'exchange_kms',
        'exchange_no_of_owners',
        'likely_purchase',
        'lead_response_id',
        'test_drive_at',
        'next_follow_up',
        
        // Page 5 - Assignment
        'company_id',
        'location_id',
        'assigned_to',
        'additional_comments',
        'status',
        
        // Audit
        'created_by',
    ];

    protected $casts = [
        'next_follow_up' => 'date',
        'test_drive_at' => 'datetime',
        'exchange_manufacture_year' => 'integer',
        'exchange_no_of_owners' => 'integer',
    ];

    // Relationships
    public function enquiryType()
    {
        return $this->belongsTo(EnquiryType::class);
    }

    public function leadType()
    {
        return $this->belongsTo(LeadType::class);
    }

    public function leadSource()
    {
        return $this->belongsTo(LeadSource::class);
    }

    public function leadStatus()
    {
        return $this->belongsTo(LeadStatus::class);
    }

    public function leadResponse()
    {
        return $this->belongsTo(StatusSource::class, 'lead_response_id');
    }

    public function segment()
    {
        return $this->belongsTo(Segment::class);
    }

    public function subSegment()
    {
        return $this->belongsTo(SubSegment::class);
    }

    public function vehicleModel()
    {
        return $this->belongsTo(VehicleModel::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function location()
    {
        return $this->belongsTo(DealerLocation::class, 'location_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Many-to-many relationships for multi-select fields
    public function variants()
    {
        return $this->belongsToMany(Variant::class, 'lead_variant');
    }

    public function fuelTypes()
    {
        return $this->belongsToMany(FuelType::class, 'lead_fuel_type');
    }

    public function transmissions()
    {
        return $this->belongsToMany(Transmission::class, 'lead_transmission');
    }

    public function colours()
    {
        return $this->belongsToMany(Colour::class, 'lead_colour');
    }

    // Assignment relationships
    public function assignments()
    {
        return $this->hasMany(LeadAssignment::class);
    }

    public function teleCallers()
    {
        return $this->belongsToMany(User::class, 'lead_assignments')
            ->withPivot('assignment_type', 'assigned_by')
            ->wherePivot('assignment_type', 'tele_caller');
    }

    public function salesConsultants()
    {
        return $this->belongsToMany(User::class, 'lead_assignments')
            ->withPivot('assignment_type', 'assigned_by')
            ->wherePivot('assignment_type', 'sales_consultant');
    }

    public function primaryTeleCaller()
    {
        return $this->teleCallers->first();
    }

    public function primarySalesConsultant()
    {
        return $this->salesConsultants->first();
    }

    // Exchange vehicle relationships
    public function exchangeMake()
    {
        return $this->belongsTo(Make::class, 'exchange_make_id');
    }

    public function exchangeBrandModel()
    {
        return $this->belongsTo(BrandModel::class, 'exchange_brand_model_id');
    }

    public function exchangeFuelType()
    {
        return $this->belongsTo(FuelType::class, 'exchange_fuel_type_id');
    }

    // Existing relationships
    public function attachments()
    {
        return $this->hasMany(LeadAttachment::class);
    }

    public function calls()
    {
        return $this->hasMany(LeadCall::class);
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function notes()
    {
        return $this->hasMany(LeadNote::class);
    }

    public function tasks()
    {
        return $this->hasMany(LeadTask::class);
    }

    public function reminders()
    {
        return $this->hasMany(LeadReminder::class);
    }

    public function tags()
    {
        return $this->hasMany(LeadTag::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'emp_code',
        'mile_id',
        'employee_name',
        'company_id',
        'is_left',
        'is_user',
        'designation_id',
        'segment_id',
        'department_id',
        'dealer_location_id',
        'subsegment_id',
        'reporting_to',
        'mobile_number',
        'email',
        'date_of_joining',
        'employee_status',
        'left_date',
        'role_id',
        'user_id',
    ];

    protected $casts = [
        'segment_id'         => 'array',
        'department_id'      => 'array',
        'dealer_location_id' => 'array',
        'subsegment_id'      => 'array',
        'reporting_to'       => 'array',
        'date_of_joining'    => 'date',
        'left_date'          => 'date',
    ];

    /**
     * Ensure array IDs are stored as integers in JSON.
     */
    protected function ensureArrayOfInts($value): array
    {
        if ($value === null || $value === '') {
            return [];
        }
        return array_values(array_map('intval', (array) $value));
    }

    public function setSegmentIdAttribute($value)
    {
        $this->attributes['segment_id'] = json_encode($this->ensureArrayOfInts($value));
    }

    public function setDepartmentIdAttribute($value)
    {
        $this->attributes['department_id'] = json_encode($this->ensureArrayOfInts($value));
    }

    public function setDealerLocationIdAttribute($value)
    {
        $this->attributes['dealer_location_id'] = json_encode($this->ensureArrayOfInts($value));
    }

    public function setSubsegmentIdAttribute($value)
    {
        $this->attributes['subsegment_id'] = json_encode($this->ensureArrayOfInts($value));
    }

    public function setReportingToAttribute($value)
    {
        $this->attributes['reporting_to'] = json_encode($this->ensureArrayOfInts($value));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function segments()
    {
        $ids = $this->segment_id ?? [];
        return Segment::whereIn('id', $ids)->get();
    }
}


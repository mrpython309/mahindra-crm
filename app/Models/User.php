<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * Mass assignable attributes
     */
    protected $fillable = [

        // AUTH
        'name',
        'email',
        'password',

        // EMPLOYEE INFO
        'emp_code',
        'mile_id',
        'company_id',

        // FLAGS
        'is_user',
        'is_left',
        'reporting_status',

        // RELATIONS
        'designation_id',
        'segment_id',
        'role_id',

        // MULTI SELECT (JSON)
        'department_id',
        'dealer_location_id',
        'subsegment_id',
        'reporting_to',


        // CONTACT
        'mobile_number',

        // STATUS
        'date_of_joining',
        'employee_status',
        'left_date',
    ];

    /**
     * Hidden attributes
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'email_verified_at'  => 'datetime',
        'password'           => 'hashed',

        // JSON
        'department_id'      => 'array',
        'dealer_location_id' => 'array',
        'subsegment_id'      => 'array',
        'reporting_to'       => 'array',

        // DATES
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

    /* ===========================
       RELATIONSHIPS
       =========================== */

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function segment()
    {
        return $this->belongsTo(Segment::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Reporting managers (self relationship using JSON)
     */
    public function reportingUsers()
    {
        return self::whereIn('id', $this->reporting_to ?? [])->get();
    }
}

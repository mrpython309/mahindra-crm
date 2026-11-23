<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadTask extends Model
{
    protected $fillable = [
        'lead_id',
        'title',
        'description',
        'due_date',
        'priority',
        'status',
        'assigned_to',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

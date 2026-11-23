<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadCall extends Model
{
    protected $fillable = [
        'lead_id',
        'call_date',
        'number',
        'type',
        'duration',
        'subject',
        'message',
        'lead_response',
        'test_drive_at',
        'likely_purchase',
        'next_follow_up_date',
        'created_by',
    ];

    protected $casts = [
        'call_date' => 'date',
        'test_drive_at' => 'datetime',
        'next_follow_up_date' => 'date',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function responseMaster()
    {
        return $this->belongsTo(StatusSource::class, 'lead_response');
    }

    public function likelyLeadType()
    {
        // Map the follow-up's likely_purchase code to the corresponding LeadType (Hot / Warm / Cold)
        return $this->belongsTo(LeadType::class, 'likely_purchase', 'likely_purchase');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

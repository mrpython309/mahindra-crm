<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusSource extends Model
{
    
    protected $fillable = ['response', 'lead_status_id'];

    public function leadStatus()
    {
        return $this->belongsTo(LeadStatus::class);
    }

}

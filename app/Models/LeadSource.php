<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadSource extends Model
{
    
    protected $fillable = ['campaign', 'enquiry_type_id'];

    public function enquiryType()
    {
        return $this->belongsTo(EnquiryType::class);
    }

}

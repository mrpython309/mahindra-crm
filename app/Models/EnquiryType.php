<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnquiryType extends Model
{
    
    protected $fillable = ['name'];

    public function leadSources()
    {
        return $this->hasMany(LeadSource::class);
    }

}

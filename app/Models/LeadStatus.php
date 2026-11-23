<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadStatus extends Model
{
    
    protected $fillable = ['name'];

    protected $casts = [
        'name' => 'string'
    ];

    public function statusSources()
    {
        return $this->hasMany(StatusSource::class);
    }

}

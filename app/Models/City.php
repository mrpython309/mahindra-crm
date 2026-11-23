<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'district_id',
        'name',
        'status',
    ];

    /**
     * Scope to get only active cities
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get the district that owns the city
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }
}

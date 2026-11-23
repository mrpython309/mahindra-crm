<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected $fillable = [
        'state_id',
        'name',
        'status',
    ];

    /**
     * Scope to get only active districts
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get the state that owns the district
     */
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Get the cities for the district
     */
    public function cities()
    {
        return $this->hasMany(City::class);
    }
}

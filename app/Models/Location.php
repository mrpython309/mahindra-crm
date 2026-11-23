<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'name',
        'state',
        'region',
        'type',
    ];

    protected $casts = [
        'type' => 'string',
    ];

    // Scopes
    public function scopeCustomerLocations($query)
    {
        return $query->where('type', 'customer');
    }

    public function scopeDealerLocations($query)
    {
        return $query->where('type', 'dealer');
    }

    // Relationships
    public function leadsAsCustomerLocation()
    {
        return $this->hasMany(Lead::class, 'location_id');
    }

    public function leadsAsDealerLocation()
    {
        return $this->hasMany(Lead::class, 'dealer_location_id');
    }
}

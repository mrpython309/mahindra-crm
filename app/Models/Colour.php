<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Colour extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'status'];

    protected $casts = [
        'status' => 'string',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function vehicleModels()
    {
        return $this->belongsToMany(VehicleModel::class, 'colour_vehicle_model');
    }

    public function variants()
    {
        return $this->belongsToMany(Variant::class, 'colour_variant');
    }
}

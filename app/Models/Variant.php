<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'vehicle_model_id', 'status'
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function vehicleModel()
    {
        return $this->belongsTo(VehicleModel::class);
    }

    public function fuelTypes()
    {
        return $this->belongsToMany(FuelType::class, 'variant_fuel_type');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function getAvailableFuelTypes()
    {
        return $this->vehicleModel ? $this->vehicleModel->fuelTypes : collect();
    }

    public function transmissions()
    {
        return $this->belongsToMany(Transmission::class, 'transmission_variant');
    }

    public function colours()
    {
        return $this->belongsToMany(Colour::class, 'colour_variant');
    }

    public function getAvailableTransmissions()
    {
        return $this->vehicleModel ? $this->vehicleModel->transmissions : collect();
    }
}

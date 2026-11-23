<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleModel extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'segment_id',
    'status',
  ];

  protected $casts = [
    'status' => 'string',
  ];

  public function segment()
  {
    return $this->belongsTo(Segment::class);
  }

  public function transmissions()
  {
      return $this->belongsToMany(Transmission::class, 'vehicle_model_transmission');
  }

  public function fuelTypes()
  {
    return $this->belongsToMany(FuelType::class, 'vehicle_model_fuel_type');
  }

  public function dealerLocations()
  {
    return $this->belongsToMany(DealerLocation::class, 'vehicle_model_dealer_location');
  }

  public function companies()
  {
    return $this->belongsToMany(Company::class, 'vehicle_model_company');
  }

  public function colours()
  {
    return $this->belongsToMany(Colour::class, 'colour_vehicle_model');
  }

  public function variants()
  {
    return $this->hasMany(Variant::class);
  }

  // Keep old methods for backward compatibility (deprecated)
  public function transmission()
  {
      return $this->transmissions()->first();
  }

  public function fuelType()
  {
    return $this->fuelTypes()->first();
  }

  public function dealerLocation()
  {
    return $this->dealerLocations()->first();
  }

  public function company()
  {
    return $this->companies()->first();
  }

  public function scopeActive($query)
  {
    return $query->where('status', 'active');
  }

  public function scopeInactive($query)
  {
    return $query->where('status', 'inactive');
  }
}

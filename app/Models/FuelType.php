<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelType extends Model
{
  use HasFactory;

  protected $fillable = ['name', 'status'];

  protected $casts = [
    'status' => 'string',
  ];

  public function vehicleModels()
  {
    return $this->hasMany(VehicleModel::class);
  }

  public function variants()
  {
    return $this->belongsToMany(Variant::class, 'variant_fuel_type');
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

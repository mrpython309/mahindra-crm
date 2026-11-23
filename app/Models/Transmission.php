<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transmission extends Model
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
    return $this->belongsToMany(Variant::class, 'transmission_variant');
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
  use HasFactory;

  protected $fillable = [
    'name', 'code', 'address_line_1', 'address_line_2', 'city', 'state', 'country', 'pincode', 'contact_person',
    'contact_number', 'email', 'status'
  ];

  protected $casts = [
    'status' => 'string',
  ];

  public function dealerLocations()
  {
    return $this->hasMany(DealerLocation::class);
  }

  public function vehicleModels()
  {
    return $this->hasMany(VehicleModel::class);
  }

  public function scopeActive($query)
  {
    return $query->where('status', 'active');
  }
}

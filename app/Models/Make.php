<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Make extends Model
{
    protected $fillable = ['name', 'status'];

    public function brandModels()
    {
        return $this->hasMany(BrandModel::class);
    }
}

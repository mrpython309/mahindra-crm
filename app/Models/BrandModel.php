<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandModel extends Model
{
    protected $fillable = ['make_id', 'name', 'status'];

    public function make()
    {
        return $this->belongsTo(Make::class);
    }
}

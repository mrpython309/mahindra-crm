<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadTag extends Model
{
    protected $fillable = [
        'lead_id',
        'tag_name',
        'color',
        'created_by',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadNote extends Model
{
    protected $fillable = [
        'lead_id',
        'note',
        'is_pinned',
        'created_by',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
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

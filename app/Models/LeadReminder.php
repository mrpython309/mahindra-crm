<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadReminder extends Model
{
    protected $fillable = [
        'lead_id',
        'title',
        'description',
        'reminder_date',
        'is_sent',
        'created_by',
    ];

    protected $casts = [
        'reminder_date' => 'datetime',
        'is_sent' => 'boolean',
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadAttachment extends Model
{
    protected $fillable = [
        'lead_id',
        'title',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
        'uploaded_by',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

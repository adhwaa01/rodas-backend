<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HandlingDocument extends Model
{
    protected $fillable = [
        'road_handling_id',
        'uploaded_by',
        'file_path',
        'description',
        'document_type',
    ];

    public function roadHandling(): BelongsTo
    {
        return $this->belongsTo(RoadHandling::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

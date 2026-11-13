<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldSurvey extends Model
{
    protected $fillable = [
        'assignment_id',
        'road_condition',
        'inspection_result',
        'recommendation',
        'latitude',
        'longitude',
        'image_path',
        'surveyed_at',
    ];

    protected function casts(): array
    {
        return [
            'surveyed_at' => 'datetime',
        ];
    }

    // Relasi dengan penugasan
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }
}

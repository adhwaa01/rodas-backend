<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Assignment extends Model
{
    protected $fillable = [
        'user_id',
        'damage_report_id',
        'assigned_by',
        'instructions',
        'due_date',
        'status',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    // Petugas yang menerima tugas
    public function surveyor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Laporan kerusakan yang ditugaskan
    public function damageReport(): BelongsTo
    {
        return $this->belongsTo(DamageReport::class);
    }

    // Admin yang memberikan tugas
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // Relasi dengan hasil survei lapangan
    public function fieldSurvey(): HasOne
    {
        return $this->hasOne(FieldSurvey::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DamageReport extends Model
{
    protected $fillable = [
        'user_id',
        'damage_type',
        'severity',
        'description',
        'latitude',
        'longitude',
        'image_path',
        'status',
        'verified_by',
        'verified_at',
        'verification_note',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    // Relasi dengan Petugas Surveyor
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relasi dengan Admin yang melakukan verifikasi
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Penugasan yang berkaitan dengan laporan
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }
}

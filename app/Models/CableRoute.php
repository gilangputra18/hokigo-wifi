<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CableRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'village_id',
        'name',
        'cable_type',
        'color',
        'core_count',
        'coordinates',
        'length_meters',
        'notes',
        'status',
    ];

    protected $casts = [
        'coordinates' => 'array',
        'length_meters' => 'integer',
    ];

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function getCableTypeLabelAttribute(): string
    {
        return match ($this->cable_type) {
            'backbone' => 'Kabel Backbone Utama',
            'distribution' => 'Kabel Distribusi Antar ODP',
            'dropcore' => 'Kabel Dropcore ke Rumah',
            default => 'Kabel Fiber Optic',
        };
    }
}

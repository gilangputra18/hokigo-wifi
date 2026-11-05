<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NetworkNode extends Model
{
    use HasFactory;

    protected $fillable = [
        'village_id',
        'name',
        'type',
        'capacity',
        'latitude',
        'longitude',
        'notes',
        'status',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'server' => 'Server Pusat / NOC',
            'starlink' => 'Dish Antena Starlink',
            'odp' => 'Kotak ODP (Fiber Optic)',
            'switch' => 'Switch / Router Distribusi',
            'pole' => 'Tiang Distribusi',
            default => 'Perangkat Jaringan',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'server' => '🖥️',
            'starlink' => '🛰️',
            'odp' => '🔲',
            'switch' => '🔀',
            'pole' => '📍',
            default => '📶',
        };
    }
}

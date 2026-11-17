<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VoucherStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'village_id',
        'period',
        'voucher_type',
        'price_per_unit',
        'initial_stock',
        'allocated_stock',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'price_per_unit' => 'decimal:2',
        'initial_stock' => 'integer',
        'allocated_stock' => 'integer',
    ];

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(VoucherAllocation::class);
    }

    /**
     * Sisa stok di sentral desa yang belum dibagikan ke penjual
     */
    public function getRemainingStockAttribute(): int
    {
        return max(0, $this->initial_stock - $this->allocated_stock);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VoucherAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'village_id',
        'voucher_seller_id',
        'voucher_stock_id',
        'period',
        'voucher_type',
        'price_per_unit',
        'quantity_given',
        'quantity_sold',
        'allocation_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'price_per_unit' => 'decimal:2',
        'quantity_given' => 'integer',
        'quantity_sold' => 'integer',
        'allocation_date' => 'date',
    ];

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(VoucherSeller::class, 'voucher_seller_id');
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(VoucherStock::class, 'voucher_stock_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(VoucherSale::class);
    }

    /**
     * Sisa stok di tangan penjual
     */
    public function getRemainingStockAttribute(): int
    {
        return max(0, $this->quantity_given - $this->quantity_sold);
    }
}

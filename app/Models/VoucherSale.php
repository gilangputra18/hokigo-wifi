<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'village_id',
        'seller_name',
        'seller_phone',
        'period',
        'voucher_type',
        'price_per_unit',
        'quantity_sold',
        'total_gross_amount',
        'fee_per_unit',
        'total_seller_fee',
        'net_amount',
        'status',
        'sale_date',
        'deposited_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'price_per_unit' => 'decimal:2',
        'quantity_sold' => 'integer',
        'total_gross_amount' => 'decimal:2',
        'fee_per_unit' => 'decimal:2',
        'total_seller_fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'sale_date' => 'date',
        'deposited_at' => 'datetime',
    ];

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Clean phone for WhatsApp links
     */
    public function getCleanPhoneAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', $this->seller_phone ?? '');
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        return $phone;
    }
}

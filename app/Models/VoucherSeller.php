<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VoucherSeller extends Model
{
    use HasFactory;

    protected $fillable = [
        'village_id',
        'name',
        'phone',
        'address',
        'default_fee_per_unit',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'default_fee_per_unit' => 'decimal:2',
        'is_active' => 'boolean',
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

    public function sales(): HasMany
    {
        return $this->hasMany(VoucherSale::class);
    }

    /**
     * Clean phone for WhatsApp links
     */
    public function getCleanPhoneAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', $this->phone ?? '');
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        return $phone;
    }
}

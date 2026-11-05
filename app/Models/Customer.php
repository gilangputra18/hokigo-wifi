<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_code',
        'name',
        'phone',
        'village_id',
        'package_id',
        'address',
        'monthly_fee',
        'due_date_day',
        'installation_date',
        'router_model',
        'pppoe_user',
        'pppoe_password',
        'ip_address',
        'status',
        'notes',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'monthly_fee' => 'decimal:2',
        'due_date_day' => 'integer',
        'installation_date' => 'date',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Formats phone to international WhatsApp standard (e.g. 08123... -> 628123...)
     */
    public function getCleanPhoneAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', $this->phone ?? '');
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (str_starts_with($phone, '8')) {
            $phone = '62' . $phone;
        }
        return $phone;
    }

    /**
     * Get invoice for current period
     */
    public function getCurrentInvoiceAttribute()
    {
        $period = now()->format('Y-m');
        return $this->invoices()->where('period', $period)->latest()->first();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Village extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'district',
        'notes',
        'system_type', // 'pppoe', 'voucher', 'hybrid'
        'latitude',
        'longitude',
        'zoom_level',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'zoom_level' => 'integer',
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function voucherSales(): HasMany
    {
        return $this->hasMany(VoucherSale::class);
    }

    public function networkNodes(): HasMany
    {
        return $this->hasMany(NetworkNode::class);
    }

    public function cableRoutes(): HasMany
    {
        return $this->hasMany(CableRoute::class);
    }

    public function isVoucherEnabled(): bool
    {
        return in_array($this->system_type, ['voucher', 'hybrid']);
    }

    public function isPppoeEnabled(): bool
    {
        return in_array($this->system_type, ['pppoe', 'hybrid', null]);
    }
}

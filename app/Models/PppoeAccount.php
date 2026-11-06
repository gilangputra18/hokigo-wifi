<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PppoeAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'village_id',
        'username',
        'password',
        'notes',
    ];

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    /**
     * Customer currently using this PPPoE username
     */
    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class, 'pppoe_user', 'username');
    }

    /**
     * Check if currently used by any customer
     */
    public function isUsed(): bool
    {
        return $this->customer()->exists();
    }
}

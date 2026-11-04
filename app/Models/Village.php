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
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}

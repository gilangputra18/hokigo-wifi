<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    protected static array $memoryCache = [];

    public static function get(string $key, $default = null): ?string
    {
        if (empty(static::$memoryCache)) {
            try {
                static::$memoryCache = static::pluck('value', 'key')->toArray();
            } catch (\Throwable $e) {
                return $default;
            }
        }
        return static::$memoryCache[$key] ?? $default;
    }

    public static function set(string $key, ?string $value, string $group = 'general'): self
    {
        static::$memoryCache[$key] = $value;
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
    }
}

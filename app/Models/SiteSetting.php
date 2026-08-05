<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    private const CACHE_KEY = 'site_settings';

    /**
     * Get a setting value, falling back to the given default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = Cache::rememberForever(self::CACHE_KEY, fn (): array => self::all()
            ->pluck('value', 'key')
            ->all());

        return array_key_exists($key, $settings) ? $settings[$key] : $default;
    }

    /**
     * Store a setting value and refresh the cache.
     */
    public static function set(string $key, mixed $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => is_scalar($value) || $value === null ? $value : json_encode($value)]
        );

        self::flush();
    }

    /**
     * Delete a setting and refresh the cache.
     */
    public static function remove(string $key): void
    {
        self::where('key', $key)->delete();

        self::flush();
    }

    /**
     * Forget the cached settings map.
     */
    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}

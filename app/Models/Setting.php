<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Business-wide settings (key → value), e.g. Setting::get('business_name').
 * All rows are cached together and the cache is cleared on every save.
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    private const CACHE_KEY = 'app_settings';

    /** Fields on Settings › Business Profile. */
    public const BUSINESS_FIELDS = ['business_name', 'phone', 'email', 'address', 'tin', 'vrn'];

    public static function cached(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            // Before the migration has run (fresh install), fall back to defaults.
            if (!Schema::hasTable('settings')) {
                return [];
            }

            return static::query()->pluck('value', 'key')->all();
        });
    }

    public static function get(string $key, $default = null)
    {
        $value = static::cached()[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Cache::forget(self::CACHE_KEY);
    }

    public static function businessName(): string
    {
        return static::get('business_name', 'Double H Cosmetics');
    }

    /** Uploaded logo, or the built-in one. */
    public static function logoUrl(): string
    {
        $logo = static::get('logo');

        return $logo && Storage::disk('public')->exists($logo)
            ? asset('storage/' . $logo)
            : asset('assets/img/logo.png');
    }
}

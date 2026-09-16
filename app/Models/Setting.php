<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type'];

    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getValue(string $key, $default = null)
    {
        try {
            return Cache::rememberForever("setting.{$key}", function () use ($key, $default) {
                $setting = self::where('key', $key)->first();
                if (!$setting) return $default;
                return ($setting->type === 'json') ? json_decode($setting->value, true) : $setting->value;
            });
        } catch (\Throwable $e) {
            $setting = self::where('key', $key)->first();
            if (!$setting) return $default;
            return ($setting->type === 'json') ? json_decode($setting->value, true) : $setting->value;
        }
    }

    /**
     * Alias of getValue() — shorthand: Setting::get('key')
     */
    public static function get(string $key, $default = null): mixed
    {
        return static::getValue($key, $default);
    }

    /**
     * Set/update a setting value.
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @return self
     */
    public static function setValue(string $key, $value, string $type = 'text')
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type]
        );

        try {
            Cache::forget("setting.{$key}");
        } catch (\Throwable $e) {
            // Ignore cache storage errors
        }

        return $setting;
    }
}

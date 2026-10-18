<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class WebsiteSetting extends Model
{
    public const CACHE_KEY = 'website.settings';

    protected $fillable = ['key', 'value'];

    /**
     * All settings as [key => value], with defaults from config/website.php
     * filled in for anything not saved yet.
     */
    public static function allWithDefaults(): array
    {
        $saved = Cache::rememberForever(self::CACHE_KEY, fn () => static::pluck('value', 'key')->all());

        $defaults = [];
        foreach (config('website.settings') as $group) {
            foreach ($group['fields'] as $key => $field) {
                $defaults[$key] = $field['default'] ?? null;
            }
        }

        return array_merge($defaults, $saved);
    }

    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Cache::forget(self::CACHE_KEY);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = Cache::rememberForever('site_settings.all', function () {
            return static::all()->keyBy('key');
        });

        return $all->has($key) ? $all[$key]->value : $default;
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('site_settings.all'));
        static::deleted(fn () => Cache::forget('site_settings.all'));
    }
}

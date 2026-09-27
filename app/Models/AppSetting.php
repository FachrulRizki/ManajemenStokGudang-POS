<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
    ];

    /**
     * Ambil nilai setting berdasarkan key.
     * Menyimpan VALUE (bukan object) di cache agar aman saat deserialisasi.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = "app_setting_val_{$key}";

        // Cek cache — nilainya berupa array ['value'=>..., 'type'=>...]
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return static::castValue($cached['value'], $cached['type']);
        }

        // Belum di cache, ambil dari DB
        try {
            $setting = static::where('key', $key)->first();
        } catch (\Exception $e) {
            return $default;
        }

        if (! $setting) {
            return $default;
        }

        // Simpan value + type saja (bukan object) ke cache
        Cache::put($cacheKey, ['value' => $setting->value, 'type' => $setting->type], 3600);

        return static::castValue($setting->value, $setting->type);
    }

    private static function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => (bool)  $value,
            'integer' => (int)   $value,
            'json'    => json_decode($value, true),
            default   => $value,
        };
    }

    /**
     * Set atau update nilai setting dan hapus cache.
     */
    public static function set(
        string $key,
        mixed  $value,
        string $type  = 'string',
        string $group = 'general',
        string $label = ''
    ): void {
        static::updateOrCreate(
            ['key'   => $key],
            [
                'value' => is_array($value) ? json_encode($value) : $value,
                'type'  => $type,
                'group' => $group,
                'label' => $label,
            ]
        );

        Cache::forget("app_setting_val_{$key}");
    }

    /**
     * Ambil semua setting dalam satu group sebagai array key => value.
     */
    public static function getGroup(string $group): array
    {
        try {
            return static::where('group', $group)
                ->get()
                ->mapWithKeys(fn($s) => [$s->key => $s->value])
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}

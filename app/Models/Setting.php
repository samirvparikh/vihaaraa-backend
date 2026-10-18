<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutIncrementing;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['key', 'value'])]
#[WithoutIncrementing]
#[WithoutTimestamps]
class Setting extends Model
{
    protected $primaryKey = 'key';

    protected $keyType = 'string';

    /** Defaults used when a key has never been saved from the admin panel. */
    public const DEFAULTS = [
        'free_shipping_min' => 499,
        'shipping_fee' => 79,
        'cod_limit' => 5000,
        'max_qty' => 10,
        'b1g1_enabled' => 1,
        'handpicked_title' => 'Handpicked For You',
        'bestsellers_title' => 'Our Best Sellers',
        'bestsellers_limit' => 8,
        'look_title' => 'Shop The Look',
        'contact_email' => 'care@vihaaraa.com',
        'contact_whatsapp' => '+91-90000 00000',
        'contact_hours' => 'For any queries, please reach out to us between 10:30 AM to 5:30 PM, Monday to Saturday.',
        'instagram_url' => '#',
        'facebook_url' => '#',
        'youtube_url' => '#',
        'pinterest_url' => '#',
    ];

    public static function values(): array
    {
        return Cache::rememberForever('settings', fn () => array_merge(
            self::DEFAULTS,
            static::query()->pluck('value', 'key')->all(),
        ));
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::values()[$key] ?? $default;
    }

    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Cache::forget('settings');
    }
}

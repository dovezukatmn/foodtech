<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    protected $fillable = [
        'key',
        'group',
        'display_name',
        'type',
        'value',
        'description',
    ];

    /**
     * Группы настроек
     */
    public const GROUP_GENERAL  = 'general';
    public const GROUP_DELIVERY = 'delivery';
    public const GROUP_IIKO     = 'iiko';
    public const GROUP_PAYMENT  = 'payment';

    /**
     * Получить все группы на русском
     */
    public static function getGroupOptions(): array
    {
        return [
            self::GROUP_GENERAL  => 'Основные',
            self::GROUP_DELIVERY => 'Доставка',
            self::GROUP_IIKO     => 'iiko',
            self::GROUP_PAYMENT  => 'Оплата',
        ];
    }

    /**
     * Типы значений
     */
    public static function getTypeOptions(): array
    {
        return [
            'string'  => 'Строка',
            'text'    => 'Текст',
            'number'  => 'Число',
            'boolean' => 'Да/Нет',
            'json'    => 'JSON',
        ];
    }

    /**
     * Получить значение настройки по ключу
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = Cache::remember("app_setting_{$key}", 3600, function () use ($key) {
            return static::where('key', $key)->first();
        });

        if (!$setting) return $default;

        return match ($setting->type) {
            'number'  => (float) $setting->value,
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json'    => json_decode($setting->value, true),
            default   => $setting->value,
        };
    }

    /**
     * Установить значение настройки
     */
    public static function set(string $key, mixed $value): void
    {
        $setting = static::where('key', $key)->first();

        if ($setting) {
            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
            $setting->update(['value' => (string) $value]);
            Cache::forget("app_setting_{$key}");
        }
    }

    /**
     * Получить настройки по группе
     */
    public static function getByGroup(string $group): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('group', $group)->orderBy('id')->get();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Promotion extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'title',
        'description',
        'promo_code',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'usage_limit',
        'usage_count',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'is_active' => 'boolean',
            'discount_value' => 'float',
            'min_order_amount' => 'float',
            'usage_limit' => 'integer',
            'usage_count' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Типы скидок на русском
     */
    public static function getDiscountTypeOptions(): array
    {
        return [
            'percent' => 'Процент (%)',
            'fixed'   => 'Фиксированная сумма (₽)',
        ];
    }

    /**
     * Проверяет, активна ли акция сейчас
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) return false;
        $now = now();
        if ($this->starts_at && $now->lt($this->starts_at)) return false;
        if ($this->ends_at && $now->gt($this->ends_at)) return false;
        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) return false;
        return true;
    }

    /**
     * Форматированное значение скидки
     */
    public function getFormattedDiscountAttribute(): string
    {
        return match ($this->discount_type) {
            'percent' => "{$this->discount_value}%",
            'fixed'   => number_format($this->discount_value, 0, ',', ' ') . ' ₽',
            default   => (string) $this->discount_value,
        };
    }
}

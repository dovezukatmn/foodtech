<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $table = 'order';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'customer_name',
        'customer_phone',
        'delivery_address',
        'comment',
        'total_amount',
        'status',
        'iiko_order_id',
        'iiko_order_number',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'iiko_order_id' => 'string',
            'total_amount' => 'float',
            'created_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    /**
     * Все доступные статусы на русском
     */
    public static function getStatusOptions(): array
    {
        return [
            'CREATED'      => 'Создан',
            'PENDING_IIKO' => 'Ожидает iiko',
            'CONFIRMED'    => 'Подтверждён',
            'COOKING'      => 'Готовится',
            'READY'        => 'Готов к выдаче',
            'DELIVERING'   => 'В доставке',
            'DELIVERED'    => 'Доставлен',
            'CANCELLED'    => 'Отменён',
        ];
    }

    /**
     * Цвета статусов для бейджей
     */
    public static function getStatusColors(): array
    {
        return [
            'CREATED'      => 'warning',
            'PENDING_IIKO' => 'info',
            'CONFIRMED'    => 'primary',
            'COOKING'      => 'warning',
            'READY'        => 'success',
            'DELIVERING'   => 'info',
            'DELIVERED'    => 'success',
            'CANCELLED'    => 'danger',
        ];
    }

    /**
     * Иконки статусов
     */
    public static function getStatusIcons(): array
    {
        return [
            'CREATED'      => 'heroicon-o-clock',
            'PENDING_IIKO' => 'heroicon-o-arrow-path',
            'CONFIRMED'    => 'heroicon-o-check',
            'COOKING'      => 'heroicon-o-fire',
            'READY'        => 'heroicon-o-check-badge',
            'DELIVERING'   => 'heroicon-o-truck',
            'DELIVERED'    => 'heroicon-o-check-circle',
            'CANCELLED'    => 'heroicon-o-x-circle',
        ];
    }

    /**
     * Допустимые переходы между статусами
     */
    public static function getAllowedTransitions(): array
    {
        return [
            'CREATED'      => ['PENDING_IIKO', 'CANCELLED'],
            'PENDING_IIKO' => ['CONFIRMED', 'CANCELLED'],
            'CONFIRMED'    => ['COOKING', 'CANCELLED'],
            'COOKING'      => ['READY', 'CANCELLED'],
            'READY'        => ['DELIVERING'],
            'DELIVERING'   => ['DELIVERED'],
            'DELIVERED'    => [],
            'CANCELLED'    => [],
        ];
    }

    /**
     * Получить допустимые следующие статусы для текущего заказа
     */
    public function getNextStatuses(): array
    {
        $transitions = self::getAllowedTransitions();
        $allowed = $transitions[$this->status] ?? [];
        $options = self::getStatusOptions();

        return collect($allowed)
            ->mapWithKeys(fn ($status) => [$status => $options[$status] ?? $status])
            ->toArray();
    }

    /**
     * Проверить, можно ли перейти в указанный статус
     */
    public function canTransitionTo(string $newStatus): bool
    {
        $transitions = self::getAllowedTransitions();
        $allowed = $transitions[$this->status] ?? [];
        return in_array($newStatus, $allowed);
    }

    /**
     * Получить метку статуса на русском
     */
    public function getStatusLabelAttribute(): string
    {
        $options = self::getStatusOptions();
        return $options[$this->status] ?? $this->status;
    }
}

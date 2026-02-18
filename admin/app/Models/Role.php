<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    /**
     * Предустановленные роли
     */
    public const ADMIN = 'admin';
    public const MANAGER = 'manager';
    public const OPERATOR = 'operator';

    /**
     * Все доступные роли
     */
    public static function getRoleOptions(): array
    {
        return [
            self::ADMIN    => 'Администратор',
            self::MANAGER  => 'Менеджер',
            self::OPERATOR => 'Оператор',
        ];
    }

    /**
     * Пользователи с данной ролью
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}

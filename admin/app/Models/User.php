<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Роль пользователя
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Проверяет, имеет ли пользователь указанную роль
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role?->name === $roleName;
    }

    /**
     * Проверяет, является ли пользователь администратором
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ADMIN);
    }

    /**
     * Проверяет, является ли пользователь менеджером
     */
    public function isManager(): bool
    {
        return $this->hasRole(Role::MANAGER);
    }

    /**
     * Проверяет, является ли пользователь оператором
     */
    public function isOperator(): bool
    {
        return $this->hasRole(Role::OPERATOR);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Все пользователи с ролью могут войти в панель
        return $this->role_id !== null;
    }
}

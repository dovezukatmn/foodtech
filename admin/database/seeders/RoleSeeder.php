<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Сидер: создание начальных ролей и администратора
     */
    public function run(): void
    {
        $admin = Role::firstOrCreate(
            ['name' => Role::ADMIN],
            [
                'display_name' => 'Администратор',
                'description'  => 'Полный доступ ко всем разделам админ-панели',
            ]
        );

        Role::firstOrCreate(
            ['name' => Role::MANAGER],
            [
                'display_name' => 'Менеджер',
                'description'  => 'Управление заказами, акциями и баннерами',
            ]
        );

        Role::firstOrCreate(
            ['name' => Role::OPERATOR],
            [
                'display_name' => 'Оператор',
                'description'  => 'Просмотр и обработка заказов',
            ]
        );

        // Назначаем роль «Администратор» всем существующим пользователям без роли
        $usersWithoutRole = User::whereNull('role_id')->count();
        if ($usersWithoutRole > 0) {
            User::whereNull('role_id')->update(['role_id' => $admin->id]);
            echo "✅ Роль «Администратор» назначена {$usersWithoutRole} пользователям без роли.\n";
        }

        // Создаём администратора по умолчанию (если нет пользователей)
        if (User::count() === 0) {
            User::create([
                'name'     => 'Администратор',
                'email'    => 'admin@foodtech.ru',
                'password' => 'password',
                'role_id'  => $admin->id,
            ]);
            echo "✅ Создан пользователь admin@foodtech.ru (пароль: password)\n";
        }
    }
}

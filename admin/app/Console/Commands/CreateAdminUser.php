<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create {email} {password} {name=Администратор} {--reset : Удалить всех существующих администраторов перед созданием}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создать нового администратора (с возможностью удаления старых)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        $name = $this->argument('name');
        $reset = $this->option('reset');

        // 1. Получаем роль администратора
        $adminRole = Role::where('name', Role::ADMIN)->first();

        if (!$adminRole) {
            $this->error('Ошибка: Роль "admin" не найдена в базе данных.');
            $this->info('Запустите сидер: php artisan db:seed --class=RoleSeeder');
            return 1;
        }

        // 2. Если передан флаг --reset, удаляем старых админов
        if ($reset) {
            // Удаляем всех пользователей с ролью admin
            $deleted = User::where('role_id', $adminRole->id)->delete();
            $this->warn("Удалено старых администраторов: {$deleted}");

            // Также можно удалить по email, если вдруг роль "слетела", но это опасно.
            // Ограничимся удалением по роли.
        }

        // 3. Проверяем, существует ли пользователь с таким email
        if (User::where('email', $email)->exists()) {
            $user = User::where('email', $email)->first();

            $this->line("Пользователь <info>{$email}</info> уже существует.");

            // Обновляем данные
            $user->password = Hash::make($password);
            $user->name = $name;
            $user->role_id = $adminRole->id; // Назначаем роль админа, если была другая
            $user->save();

            $this->info("✅ Пароль и роль пользователя {$email} обновлены.");
        } else {
            // Создаем нового
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role_id' => $adminRole->id,
            ]);

            $this->info("✅ Администратор {$email} успешно создан.");
        }

        return 0;
    }
}

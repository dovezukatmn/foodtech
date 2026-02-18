<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
            $url = \Illuminate\Support\Facades\Config::get('app.url');
            if (str_contains($url, 'https://')) {
               \Illuminate\Support\Facades\URL::forceRootUrl($url);
            }
        }

        \Log::info('AppServiceProvider booted. Environment: ' . $this->app->environment());

        // Настройка меню AdminLTE
        \Illuminate\Support\Facades\Event::listen(
            \JeroenNoten\LaravelAdminLte\Events\BuildingMenu::class,
            function (\JeroenNoten\LaravelAdminLte\Events\BuildingMenu $event) {
                // Главная
                $event->menu->add([
                    'text' => 'Главная',
                    'url'  => 'admin',
                    'icon' => 'fas fa-fw fa-tachometer-alt',
                ]);

                // Пользователи
                $event->menu->add('УПРАВЛЕНИЕ ДОСТУПОМ');
                $event->menu->add([
                    'text' => 'Пользователи',
                    'url'  => 'admin/users',
                    'icon' => 'fas fa-fw fa-users',
                    'active' => ['admin/users*'],
                ]);

                // Каталог
                $event->menu->add('КАТАЛОГ');
                $event->menu->add([
                    'text' => 'Категории',
                    'url'  => 'admin/categories',
                    'icon' => 'fas fa-fw fa-list',
                    'active' => ['admin/categories*'],
                ]);
                $event->menu->add([
                    'text' => 'Товары',
                    'url'  => 'admin/products',
                    'icon' => 'fas fa-fw fa-hamburger',
                    'active' => ['admin/products*'],
                ]);
                $event->menu->add([
                    'text' => 'Модификаторы',
                    'url'  => 'admin/modifiers',
                    'icon' => 'fas fa-fw fa-cubes',
                    'active' => ['admin/modifiers*'],
                ]);
                $event->menu->add([
                    'text' => 'Группы модификаторов',
                    'url'  => 'admin/modifier-groups',
                    'icon' => 'fas fa-fw fa-layer-group',
                    'active' => ['admin/modifier-groups*'],
                ]);

                // Заказы
                $event->menu->add('ПРОДАЖИ');
                $event->menu->add([
                    'text' => 'Заказы',
                    'url'  => 'admin/orders',
                    'icon' => 'fas fa-fw fa-shopping-cart',
                    'label' => \App\Models\Order::where('status', 'new')->count(),
                    'label_color' => 'success',
                    'active' => ['admin/orders*'],
                ]);
                $event->menu->add([
                    'text' => 'Канбан',
                    'url'  => 'admin/orders/kanban',
                    'icon' => 'fas fa-fw fa-columns',
                    'active' => ['admin/orders/kanban*'],
                ]);

                // Настройки
                $event->menu->add('СИСТЕМА');
                $event->menu->add([
                    'text' => 'Профиль',
                    'url'  => 'admin/profile',
                    'icon' => 'fas fa-fw fa-user',
                ]);
            }
        );
    }
}

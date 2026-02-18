<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Navigation\NavigationGroup;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()

            // Брендинг
            ->brandName('FoodTech')
            ->brandLogo(null)
            ->favicon(null)

            // Премиальная цветовая палитра
            ->colors([
                'primary' => Color::Violet,
                'danger'  => Color::Rose,
                'info'    => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'gray'    => Color::Slate,
            ])

            // Тёмная тема по умолчанию
            ->darkMode(true)

            // Шрифт Inter (Google Fonts)
            ->font('Inter')

            // Кастомные стили (без Vite — прямое подключение)
            ->renderHook(
                'panels::head.end',
                fn () => '<link rel="stylesheet" href="/css/admin-theme.css">',
            )

            // Боковая панель
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('280px')
            ->collapsedSidebarWidth('70px')

            // Навигация
            ->navigationGroups([
                NavigationGroup::make('Дашборд')
                    ->icon('heroicon-o-home')
                    ->collapsed(false),
                NavigationGroup::make('Меню')
                    ->icon('heroicon-o-book-open')
                    ->collapsed(false),
                NavigationGroup::make('Управление')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(false),
                NavigationGroup::make('Маркетинг')
                    ->icon('heroicon-o-megaphone')
                    ->collapsed(true),
            ])

            // Глобальный поиск
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->globalSearchFieldKeyBindingSuffix()

            // Breadcrumbs
            ->breadcrumbs(true)

            // Макет
            ->maxContentWidth('full')
            ->topNavigation(false)

            // Хлебные крошки в заголовке
            ->renderHook(
                'panels::body.start',
                fn () => view('filament.components.custom-header'),
            )

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}

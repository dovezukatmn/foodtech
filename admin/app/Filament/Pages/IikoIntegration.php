<?php

namespace App\Filament\Pages;

use App\Services\IikoService;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class IikoIntegration extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationLabel = 'iiko Интеграция';
    protected static ?string $title = 'Интеграция с iiko';
    protected static ?string $navigationGroup = 'Управление';
    protected static ?int $navigationSort = 6;
    protected static string $view = 'filament.pages.iiko-integration';

    public ?array $connectionStatus = null;
    public ?array $syncResult = null;

    /**
     * Проверить подключение к iiko
     */
    public function testConnection(): void
    {
        $iiko = new IikoService();
        $this->connectionStatus = $iiko->testConnection();

        if ($this->connectionStatus['success']) {
            Notification::make()
                ->title('Подключение установлено')
                ->body('API iiko доступен')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Ошибка подключения')
                ->body($this->connectionStatus['message'])
                ->danger()
                ->send();
        }
    }

    /**
     * Синхронизировать категории
     */
    public function syncCategories(): void
    {
        $iiko = new IikoService();
        $result = $iiko->syncCategories();

        if ($result['error']) {
            Notification::make()
                ->title('Ошибка синхронизации')
                ->body($result['error'])
                ->danger()
                ->send();
        } else {
            Notification::make()
                ->title('Категории синхронизированы')
                ->body("Обработано: {$result['synced']}")
                ->success()
                ->send();
        }
    }

    /**
     * Синхронизировать продукты
     */
    public function syncProducts(): void
    {
        $iiko = new IikoService();
        $result = $iiko->syncProducts();

        if ($result['error']) {
            Notification::make()
                ->title('Ошибка синхронизации')
                ->body($result['error'])
                ->danger()
                ->send();
        } else {
            Notification::make()
                ->title('Продукты синхронизированы')
                ->body("Обработано: {$result['synced']}")
                ->success()
                ->send();
        }
    }

    /**
     * Полная синхронизация
     */
    public function syncAll(): void
    {
        $iiko = new IikoService();
        $result = $iiko->syncAll();

        $catCount = $result['categories']['synced'] ?? 0;
        $prodCount = $result['products']['synced'] ?? 0;
        $errors = array_filter([
            $result['categories']['error'] ?? null,
            $result['products']['error'] ?? null,
        ]);

        if (empty($errors)) {
            Notification::make()
                ->title('Синхронизация завершена')
                ->body("Категорий: {$catCount}, Продуктов: {$prodCount}")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Синхронизация завершена с ошибками')
                ->body(implode('; ', $errors))
                ->warning()
                ->send();
        }

        $this->syncResult = $result;
    }
}

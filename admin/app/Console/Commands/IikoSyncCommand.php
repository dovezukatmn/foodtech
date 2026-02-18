<?php

namespace App\Console\Commands;

use App\Services\IikoService;
use Illuminate\Console\Command;

class IikoSyncCommand extends Command
{
    protected $signature = 'iiko:sync {--categories : Только категории} {--products : Только продукты}';
    protected $description = 'Синхронизация меню из iiko Cloud';

    public function handle(): int
    {
        $iiko = new IikoService();

        // Проверяем подключение
        $this->info('Проверка подключения к iiko...');
        $test = $iiko->testConnection();

        if (!$test['success']) {
            $this->error("❌ {$test['message']}");
            return self::FAILURE;
        }

        $this->info('✅ Подключение установлено');

        if ($this->option('categories')) {
            $this->info('Синхронизация категорий...');
            $result = $iiko->syncCategories();
            $this->info("✅ Категорий: {$result['synced']}");
        } elseif ($this->option('products')) {
            $this->info('Синхронизация продуктов...');
            $result = $iiko->syncProducts();
            $this->info("✅ Продуктов: {$result['synced']}");
        } else {
            $this->info('Полная синхронизация...');
            $result = $iiko->syncAll();
            $this->info("✅ Категорий: {$result['categories']['synced']}, Продуктов: {$result['products']['synced']}");
        }

        return self::SUCCESS;
    }
}

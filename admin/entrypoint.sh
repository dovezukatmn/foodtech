#!/bin/bash
set -e

cd /var/www

# Установка PHP-зависимостей
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "==> Установка Composer-зависимостей..."
    composer install --no-interaction --optimize-autoloader
fi

# Установка JS-зависимостей и сборка ассетов
if [ ! -d "node_modules" ]; then
    echo "==> Установка NPM-зависимостей..."
    npm install
fi

# Генерация APP_KEY если нет
php artisan key:generate --force --no-interaction 2>/dev/null || true

# Публикация ассетов Filament
echo "==> Публикация ассетов Filament..."
php artisan filament:install --panels --no-interaction 2>/dev/null || true
php artisan vendor:publish --tag=filament-config --force 2>/dev/null || true

# Сборка Vite-ассетов
echo "==> Сборка фронтенд-ассетов..."
npm run build 2>/dev/null || true

# Запуск миграций Laravel (только для таблиц Laravel — users, sessions и т.д.)
echo "==> Запуск миграций..."
php artisan migrate --force --no-interaction 2>/dev/null || true

# Очистка кешей
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Создание symlink на storage
php artisan storage:link 2>/dev/null || true

echo "==> Админ-панель готова к работе!"
echo "==> Запуск PHP-FPM..."

exec php-fpm

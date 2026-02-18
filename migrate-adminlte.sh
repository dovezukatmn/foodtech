#!/bin/bash
# migrate-adminlte.sh — Миграция на AdminLTE
# Запуск: sudo bash migrate-adminlte.sh

if [ "$EUID" -ne 0 ]; then
    echo "❌ Запустите с sudo!"
    exit 1
fi

PROJECT_DIR="/opt/foodtech/admin"
LOG_FILE="/opt/foodtech/adminlte_install.log"
exec > >(tee -a "$LOG_FILE") 2>&1

echo "🚀 ЗАПУСК МИГРАЦИИ НА ADMINLTE..."
date

# 0. Исправление прав доступа и Git
echo "🛠️  [0/5] Настройка окружения..."
git config --global --add safe.directory /opt/foodtech
git config --global --add safe.directory /opt/foodtech/admin

chown -R root:root "$PROJECT_DIR"
chmod -R 755 "$PROJECT_DIR"

cd "$PROJECT_DIR"

# 1. Установка пакета
echo "📦 [1/5] Установка AdminLTE и UI..."
# Разрешаем запуск от root так как мы в системной папке
export COMPOSER_ALLOW_SUPERUSER=1
composer require jeroennoten/laravel-adminlte --no-interaction
composer require laravel/ui --no-interaction

# 2. Публикация ассетов
echo "✨ [2/5] Публикация ресурсов..."
php artisan adminlte:install --force --type=full --no-interaction
php artisan ui bootstrap --auth --no-interaction

# 3. Сборка фронтенда
echo "🎨 [3/5] Сборка стилей..."
# Исправляем права для npm
npm install --unsafe-perm
npm run build

# 4. Очистка кэша
echo "🧹 [4/5] Очистка..."
php artisan optimize:clear
php artisan view:cache
php artisan config:cache

# 5. Права доступа для веб-сервера и перезапуск
echo "♻️  [5/5] Финализация..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
systemctl restart php8.2-fpm nginx

echo "✅ ГОТОВО! AdminLTE установлен."

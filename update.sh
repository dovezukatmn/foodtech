#!/bin/bash
# Скрипт автоматического обновления проекта FoodTech
# Запуск: sudo bash update.sh

if [ "$EUID" -ne 0 ]; then
    echo "❌ Пожалуйста, запустите от root: sudo bash update.sh"
    exit 1
fi

echo "⬇️  [1/4] Получение обновлений кода..."
cd /opt/foodtech
git pull

echo "🚀 [2/4] Обновление Laravel..."
cd /opt/foodtech/admin

# Сброс кэша перед миграциями
php artisan config:clear

# Миграции базы данных (если есть новые)
php artisan migrate --force

echo "🧹 [3/4] Оптимизация кэша..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# На всякий случай обновляем права
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "🔄 [4/4] Перезапуск сервисов..."
systemctl restart php8.2-fpm
# systemctl restart queue-worker # Раскомментировать, когда появится очередь

echo "✅ Готово! Проект успешно обновлён."

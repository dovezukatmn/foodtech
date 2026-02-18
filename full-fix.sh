#!/bin/bash
# full-fix.sh — Полное исправление конфигурации сервера
# Запуск: sudo bash full-fix.sh

if [ "$EUID" -ne 0 ]; then
    echo "❌ Запустите с sudo!"
    exit 1
fi

LOG_FILE="/opt/foodtech/fix.log"
exec > >(tee -a "$LOG_FILE") 2>&1

echo "🛠 ЗАПУСК ПОЛНОГО ИСПРАВЛЕНИЯ..."
date

# 1. Принудительная установка HTTPS URL в .env
echo "📝 [1/6] Настройка .env (APP_URL)..."
ENV_FILE="/opt/foodtech/.env"
if [ -f "$ENV_FILE" ]; then
    # Бэкап
    cp "$ENV_FILE" "${ENV_FILE}.bak.$(date +%s)"
    
    # Меняем или добавляем APP_URL
    if grep -q "APP_URL=" "$ENV_FILE"; then
        sed -i 's|^APP_URL=.*|APP_URL=https://vezuroll.ru|g' "$ENV_FILE"
    else
        echo "APP_URL=https://vezuroll.ru" >> "$ENV_FILE"
    fi
    
    # Добавляем ASSET_URL на всякий случай
    if grep -q "ASSET_URL=" "$ENV_FILE"; then
        sed -i 's|^ASSET_URL=.*|ASSET_URL=https://vezuroll.ru|g' "$ENV_FILE"
    else
        echo "ASSET_URL=https://vezuroll.ru" >> "$ENV_FILE"
    fi
    
    echo "   -> APP_URL установлен в https://vezuroll.ru"
else
    echo "❌ Файл .env не найден! Создаю новый..."
    echo "APP_URL=https://vezuroll.ru" > "$ENV_FILE"
fi

# 2. Полная перезапись конфига Nginx
echo "lock [2/6] Перезапись конфига Nginx..."
cat > /etc/nginx/sites-available/foodtech << 'NGINX_CONF'
server {
    listen 80;
    server_name _;
    
    root /opt/foodtech/admin/public;
    index index.php index.html;
    
    client_max_body_size 50M;

    # Логи ошибок
    error_log /var/log/nginx/foodtech_error.log;
    access_log /var/log/nginx/foodtech_access.log;

    # Gzip
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml text/javascript image/svg+xml;

    # Статика
    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot|map)$ {
        expires 7d;
        access_log off;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    # PHP
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        
        # КРИТИЧЕСКИ ВАЖНО ДЛЯ HTTPS ЗА ПРОКСИ/БЕЗ SSL В NGINX
        fastcgi_param HTTPS on;
        fastcgi_param HTTP_SCHEME https;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
NGINX_CONF

ln -sf /etc/nginx/sites-available/foodtech /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
echo "   -> Конфиг Nginx обновлен."

# 3. Копирование стилей (на случай если не скопировались)
echo "🎨 [3/6] Копирование стилей..."
mkdir -p /opt/foodtech/admin/public/css
cp -f /opt/foodtech/admin/resources/css/filament/admin/theme.css /opt/foodtech/admin/public/css/admin-theme.css 2>/dev/null || echo "   ⚠️ Исходный CSS не найден, пропускаем."

# 4. Исправление прав
echo "🔑 [4/6] Исправление прав..."
chown -R www-data:www-data /opt/foodtech/admin/storage /opt/foodtech/admin/bootstrap/cache /opt/foodtech/admin/public
chmod -R 775 /opt/foodtech/admin/storage /opt/foodtech/admin/bootstrap/cache

# 5. Очистка кэша Laravel
echo "🧹 [5/6] Очистка кэша Laravel..."
cd /opt/foodtech/admin
php artisan config:clear
php artisan route:clear
php artisan view:clear
chmod -R 777 storage/framework/views 2>/dev/null || true

# 6. Перезапуск
echo "♻️  [6/6] Перезапуск сервисов..."
systemctl restart php8.2-fpm
systemctl restart nginx

echo "✅ ГОТОВО! Проверьте https://vezuroll.ru/admin"
echo "📜 Лог записан в $LOG_FILE"

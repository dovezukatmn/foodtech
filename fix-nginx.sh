#!/bin/bash
# fix-nginx.sh — Исправление HTTPS в Nginx

if [ "$EUID" -ne 0 ]; then
    echo "❌ Запустите от root: sudo bash fix-nginx.sh"
    exit 1
fi

CONF="/etc/nginx/sites-available/foodtech"

echo "🔧 Исправление Nginx конфига..."

# Проверяем, есть ли уже этот параметр
if grep -q "fastcgi_param HTTPS on;" "$CONF"; then
    echo "✅ Параметр уже добавлен."
else
    # Добавляем fastcgi_param HTTPS on; после fastcgi_param PATH_INFO
    sed -i '/fastcgi_param PATH_INFO/a \        fastcgi_param HTTPS on;' "$CONF"
    echo "✅ Параметр добавлен."
fi

# Проверка и рестарт
nginx -t && systemctl restart nginx

echo "🚀 Nginx перезапущен. Пробуйте войти!"

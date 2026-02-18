#!/bin/bash
# =============================================
# FoodTech — Настройка SSL-сертификата
# Запуск: sudo bash setup-ssl.sh yourdomain.ru admin@yourdomain.ru
# =============================================

set -e

DOMAIN=$1
EMAIL=$2

if [ -z "$DOMAIN" ] || [ -z "$EMAIL" ]; then
    echo ""
    echo "  Использование: sudo bash setup-ssl.sh yourdomain.ru admin@yourdomain.ru"
    echo ""
    exit 1
fi

PROJECT_DIR="/opt/foodtech"
cd "$PROJECT_DIR"

echo ""
echo "  🔒 Настройка SSL для: $DOMAIN"
echo ""

# 1. Получение сертификата
echo "[1/3] Получение SSL-сертификата..."
docker compose -f docker-compose.prod.yml run --rm certbot \
    certbot certonly --webroot \
    --webroot-path=/var/www/certbot \
    --email "$EMAIL" \
    --agree-tos \
    --no-eff-email \
    -d "$DOMAIN" \
    -d "www.$DOMAIN"

# 2. Обновление Nginx конфигурации с SSL
echo "[2/3] Настройка Nginx с SSL..."
cat > "$PROJECT_DIR/docker/nginx/conf.d/app.conf" << NGINX_CONF
# HTTP → HTTPS redirect
server {
    listen 80;
    server_name $DOMAIN www.$DOMAIN;

    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
    }

    location / {
        return 301 https://\$host\$request_uri;
    }
}

# HTTPS
server {
    listen 443 ssl http2;
    server_name $DOMAIN www.$DOMAIN;

    ssl_certificate /etc/nginx/ssl/live/$DOMAIN/fullchain.pem;
    ssl_certificate_key /etc/nginx/ssl/live/$DOMAIN/privkey.pem;

    # SSL параметры
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 1d;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    index index.php index.html;
    root /var/www/public;
    client_max_body_size 50M;

    # Gzip
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml text/javascript image/svg+xml;

    # Статика
    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        access_log off;
        add_header Cache-Control "public, immutable";
        try_files \$uri =404;
    }

    # PHP
    location ~ \.php$ {
        try_files \$uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass admin:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_param PATH_INFO \$fastcgi_path_info;
        fastcgi_read_timeout 300;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
    }

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
        gzip_static on;
    }

    location ~ /\. {
        deny all;
    }
}
NGINX_CONF

# 3. Перезапуск Nginx
echo "[3/3] Перезапуск Nginx..."
docker compose -f docker-compose.prod.yml restart nginx

# 4. Обновляем .env
sed -i "s|APP_URL=.*|APP_URL=https://$DOMAIN|" "$PROJECT_DIR/.env"

echo ""
echo "  🎉 SSL настроен!"
echo "  🌐 https://$DOMAIN/admin"
echo ""
echo "  Автообновление: сертификат обновляется автоматически через certbot контейнер."
echo ""

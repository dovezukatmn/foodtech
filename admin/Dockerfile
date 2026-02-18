FROM php:8.2-fpm

# Установка системных зависимостей (включая libicu-dev для intl!)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    libpq-dev \
    nodejs \
    npm

# Очистка кеша
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Установка PHP-расширений
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip intl

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Создаём entrypoint с Unix-переносами строк (LF)
RUN printf '#!/bin/bash\n\
    set -e\n\
    cd /var/www\n\
    echo "==> [1/6] Установка Composer-зависимостей..."\n\
    composer update --no-interaction --optimize-autoloader 2>&1\n\
    echo "==> [2/6] Установка NPM-зависимостей..."\n\
    npm install 2>&1 || true\n\
    echo "==> [3/6] Генерация APP_KEY..."\n\
    php artisan key:generate --force --no-interaction 2>/dev/null || true\n\
    echo "==> [4/6] Запуск миграций..."\n\
    php artisan migrate --force --no-interaction 2>&1 || true\n\
    echo "==> [5/6] Сборка фронтенд-ассетов..."\n\
    npm run build 2>&1 || true\n\
    echo "==> [6/6] Очистка кешей..."\n\
    php artisan config:clear 2>/dev/null || true\n\
    php artisan route:clear 2>/dev/null || true\n\
    php artisan view:clear 2>/dev/null || true\n\
    php artisan storage:link 2>/dev/null || true\n\
    echo ""\n\
    echo "========================================"\n\
    echo "  Админ-панель готова к работе!"\n\
    echo "  http://localhost:8080/admin"\n\
    echo "========================================"\n\
    echo ""\n\
    echo "==> Запуск PHP-FPM..."\n\
    exec php-fpm\n' > /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

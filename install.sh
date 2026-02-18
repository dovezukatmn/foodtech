#!/bin/bash
# =============================================
# FoodTech Admin — Установочный скрипт
# Для Ubuntu 24.04 LTS (чистый сервер)
# VPS Джино / любой VPS
# =============================================
# Запуск: sudo bash install.sh
# =============================================

set -e

# Цвета
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m'

PROJECT_DIR="/opt/foodtech"

echo ""
echo -e "${PURPLE}╔══════════════════════════════════════╗${NC}"
echo -e "${PURPLE}║   🚀 FoodTech Admin — Установка     ║${NC}"
echo -e "${PURPLE}╚══════════════════════════════════════╝${NC}"
echo ""

# === Проверка root ===
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}❌ Запустите от root: sudo bash install.sh${NC}"
    exit 1
fi

# === 1. Обновление системы ===
echo -e "${BLUE}[1/7]${NC} Обновление системы..."
apt-get update -qq
apt-get upgrade -y -qq
echo -e "${GREEN}  ✅ Система обновлена${NC}"

# === 2. Установка Docker ===
echo -e "${BLUE}[2/7]${NC} Установка Docker..."
if command -v docker &> /dev/null; then
    echo -e "${YELLOW}  ⏭️  Docker уже установлен$(docker --version)${NC}"
else
    apt-get install -y -qq ca-certificates curl gnupg lsb-release
    install -m 0755 -d /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
    chmod a+r /etc/apt/keyrings/docker.gpg

    echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null

    apt-get update -qq
    apt-get install -y -qq docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

    systemctl enable docker
    systemctl start docker
    echo -e "${GREEN}  ✅ Docker установлен${NC}"
fi

# === 3. Установка утилит ===
echo -e "${BLUE}[3/7]${NC} Установка утилит..."
apt-get install -y -qq git curl wget htop ufw fail2ban
echo -e "${GREEN}  ✅ Утилиты установлены${NC}"

# === 4. Настройка файрвола ===
echo -e "${BLUE}[4/7]${NC} Настройка файрвола..."
ufw --force reset > /dev/null 2>&1
ufw default deny incoming > /dev/null 2>&1
ufw default allow outgoing > /dev/null 2>&1
ufw allow ssh > /dev/null 2>&1
ufw allow 80/tcp > /dev/null 2>&1
ufw allow 443/tcp > /dev/null 2>&1
ufw --force enable > /dev/null 2>&1
echo -e "${GREEN}  ✅ Файрвол настроен (SSH, HTTP, HTTPS)${NC}"

# === 5. Копирование проекта ===
echo -e "${BLUE}[5/7]${NC} Подготовка проекта..."
mkdir -p "$PROJECT_DIR"
mkdir -p "$PROJECT_DIR/docker/nginx/ssl"

if [ ! -f "$PROJECT_DIR/.env" ]; then
    if [ -f "$PROJECT_DIR/.env.production" ]; then
        cp "$PROJECT_DIR/.env.production" "$PROJECT_DIR/.env"
        echo -e "${YELLOW}  ⚠️  .env создан из шаблона — ЗАПОЛНИТЕ ДАННЫЕ!${NC}"
    fi
fi

# Права на файлы Laravel
if [ -d "$PROJECT_DIR/admin" ]; then
    chmod -R 775 "$PROJECT_DIR/admin/storage" 2>/dev/null || true
    chmod -R 775 "$PROJECT_DIR/admin/bootstrap/cache" 2>/dev/null || true
    chown -R www-data:www-data "$PROJECT_DIR/admin/storage" 2>/dev/null || true
    chown -R www-data:www-data "$PROJECT_DIR/admin/bootstrap/cache" 2>/dev/null || true
fi

echo -e "${GREEN}  ✅ Проект подготовлен в ${PROJECT_DIR}${NC}"

# === 6. Запуск Docker ===
echo -e "${BLUE}[6/7]${NC} Сборка и запуск контейнеров..."
cd "$PROJECT_DIR"

docker compose -f docker-compose.prod.yml build --no-cache
docker compose -f docker-compose.prod.yml up -d

echo -e "${GREEN}  ✅ Контейнеры запущены${NC}"

# === 7. Подождать и засидировать ===
echo -e "${BLUE}[7/7]${NC} Ожидание инициализации (30 сек)..."
sleep 30

echo -e "${BLUE}  Запуск сидеров...${NC}"
docker compose -f docker-compose.prod.yml exec -T admin php artisan db:seed --class=RoleSeeder --force 2>&1 || true
docker compose -f docker-compose.prod.yml exec -T admin php artisan db:seed --class=AppSettingSeeder --force 2>&1 || true
echo -e "${GREEN}  ✅ Данные засижены${NC}"

# === Итог ===
SERVER_IP=$(curl -s ifconfig.me 2>/dev/null || hostname -I | awk '{print $1}')

echo ""
echo -e "${PURPLE}╔══════════════════════════════════════╗${NC}"
echo -e "${PURPLE}║   🎉 Установка завершена!            ║${NC}"
echo -e "${PURPLE}╚══════════════════════════════════════╝${NC}"
echo ""
echo -e "${GREEN}  🌐 Админ-панель: ${NC}http://${SERVER_IP}/admin"
echo -e "${GREEN}  📧 Логин:       ${NC}admin@foodtech.ru"
echo -e "${GREEN}  🔑 Пароль:      ${NC}password"
echo ""
echo -e "${YELLOW}  ⚠️  ОБЯЗАТЕЛЬНО:${NC}"
echo -e "    1. Откройте ${PROJECT_DIR}/.env"
echo -e "    2. Измените POSTGRES_PASSWORD на надёжный пароль"
echo -e "    3. Измените пароль admin@foodtech.ru через панель"
echo -e "    4. Для SSL: настройте домен и запустите certbot"
echo ""
echo -e "${BLUE}  📖 Полезные команды:${NC}"
echo -e "    docker compose -f docker-compose.prod.yml logs -f admin"
echo -e "    docker compose -f docker-compose.prod.yml exec admin php artisan tinker"
echo -e "    docker compose -f docker-compose.prod.yml restart"
echo ""

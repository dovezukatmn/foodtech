@echo off
REM =============================================
REM FoodTech — Загрузка проекта на VPS
REM Запуск: upload.bat user@ip-адрес
REM =============================================

set SERVER=%1

if "%SERVER%"=="" (
    echo.
    echo  Использование: upload.bat root@123.45.67.89
    echo.
    exit /b 1
)

echo.
echo  ╔══════════════════════════════════════╗
echo  ║  📤 Загрузка FoodTech на сервер      ║
echo  ╚══════════════════════════════════════╝
echo.

REM Создаём папку на сервере
echo [1/3] Создание папки на сервере...
ssh %SERVER% "mkdir -p /opt/foodtech"

REM Загружаем файлы (исключаем ненужное)
echo [2/3] Загрузка файлов...
scp -r admin %SERVER%:/opt/foodtech/
scp -r docker %SERVER%:/opt/foodtech/
scp docker-compose.prod.yml %SERVER%:/opt/foodtech/
scp .env.production %SERVER%:/opt/foodtech/
scp install.sh %SERVER%:/opt/foodtech/
scp setup-ssl.sh %SERVER%:/opt/foodtech/

REM Запускаем установку
echo [3/3] Запуск установки...
ssh %SERVER% "cd /opt/foodtech && sudo bash install.sh"

echo.
echo  ✅ Готово! Сервер настроен.
echo.

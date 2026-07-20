@echo off
setlocal
cd /d "%~dp0"
set "DOCKER_CONFIG=%~dp0storage\framework\docker-cli-clean"
set "DOCKER_HOST=npipe:////./pipe/dockerDesktopLinuxEngine"
if not exist "%DOCKER_CONFIG%" mkdir "%DOCKER_CONFIG%"

echo ==================================
echo STARTING DATN SERVICES
echo ==================================

REM Docker - CHI dung stack LOCAL (mysql/redis/phpmyadmin/mailpit).
REM docker-compose.yml la cau hinh PRODUCTION (full stack + Caddy), KHONG dung o day.
docker compose -f docker-compose.local.yml up -d
if errorlevel 1 (
    echo.
    echo Docker services could not start. Make sure Docker Desktop is running.
    pause
    exit /b 1
)

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\wait-for-dev-ports.ps1"
if errorlevel 1 (
    echo Docker containers are running, but Windows port forwarding is unavailable.
    echo Restarting Docker Desktop once...
    docker desktop restart
    if errorlevel 1 (
        echo Docker Desktop restart failed.
        pause
        exit /b 1
    )

    docker compose -f docker-compose.local.yml up -d
    if errorlevel 1 (
        echo Docker services could not restart.
        pause
        exit /b 1
    )

    powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\wait-for-dev-ports.ps1"
    if errorlevel 1 (
        echo Docker ports 3306, 6379 and 8080 are still unavailable.
        pause
        exit /b 1
    )
)

docker exec attendia_mysql_local mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS db_quan_ly_diem_danh_v2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if errorlevel 1 (
    echo Could not ensure the MySQL database exists.
    pause
    exit /b 1
)

set "RESET_DATABASE=0"
if /I "%~1"=="fresh" set "RESET_DATABASE=1"
if /I "%~1"=="reset" set "RESET_DATABASE=1"

if "%RESET_DATABASE%"=="1" (
    echo.
    echo Rebuilding database with php artisan migrate:fresh --seed...
    echo Warning: all existing local data will be deleted.
    php artisan migrate:fresh --seed --force --no-interaction
    if errorlevel 1 (
        echo Database rebuild failed.
        pause
        exit /b 1
    )
) else (
    echo.
    echo Running database migrations...
    php artisan migrate --force --no-interaction
    if errorlevel 1 (
        echo Database migration failed.
        echo If you edited an old migration file, run: start-dev.bat fresh
        pause
        exit /b 1
    )

    echo.
    echo Running plan seeders...
    php artisan db:seed --class=PlanSeeder --force --no-interaction
    if errorlevel 1 (
        echo Plan data seeding failed.
        pause
        exit /b 1
    )

    echo.
    echo Checking demo data...
    php -r "require 'vendor/autoload.php'; $app = require 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); $demoExists = App\Models\User::whereIn('email', ['admin@example.com', 'teacher@example.com', 'student1@example.com'])->exists() || App\Models\CourseClass::whereIn('join_key', ['WEB-2026-01', 'DB-2026-01', 'SE-2026-01'])->exists(); exit($demoExists ? 0 : 1);"
    if errorlevel 1 (
        echo Demo data not found. Running DemoSeeder once...
        php artisan db:seed --class=DemoSeeder --force --no-interaction
        if errorlevel 1 (
            echo Demo data seeding failed.
            pause
            exit /b 1
        )
    ) else (
        echo Demo data already exists. Skipping DemoSeeder to avoid duplicate emails and class codes.
        echo To rebuild demo data from scratch, run: start-dev.bat fresh
    )
)

echo.
echo Clearing Laravel cache...
php artisan optimize:clear
if errorlevel 1 (
    echo Laravel cache clear failed.
    pause
    exit /b 1
)

REM Laravel
start "Laravel" cmd /k "cd /d ""%~dp0"" && php artisan serve"

REM Queue Worker - nghe 3 queue theo do uu tien: imports > mails > default
start "Queue Worker" cmd /k "cd /d ""%~dp0"" && php artisan queue:work redis --queue=imports,mails,default --tries=3"

REM Scheduler (rut hang cho Outbox email import moi phut + cac lenh dinh ky khac)
start "Scheduler" cmd /k "cd /d ""%~dp0"" && php artisan schedule:work"

REM Vite
start "Vite" cmd /k "cd /d ""%~dp0"" && npm.cmd run dev -- --host 127.0.0.1 --strictPort"

REM Socket.IO
start "Socket.IO" cmd /k "cd /d ""%~dp0"" && node server.cjs"

REM ngrok - tunnel public de MoMo goi IPN ve (cong 8000)
REM Lan dau dung: cai ngrok roi chay  ngrok config add-authtoken <token>  (lay tai dashboard.ngrok.com)
where ngrok >nul 2>nul
if errorlevel 1 (
    echo [ngrok] Chua cai ngrok hoac chua co trong PATH - bo qua tunnel MoMo.
    echo         Cai ngrok: https://ngrok.com/download  roi mo lai start-dev.
) else (
    REM Mac dinh: URL ngrok doi moi lan chay -> phai cap nhat MOMO_IPN_URL trong .env.
    start "ngrok" cmd /k "ngrok http --url=https://undefined-sapling-glorify.ngrok-free.dev 8000"
    REM De URL co dinh (khoi sua .env moi lan): xin 1 static domain mien phi tai
    REM dashboard.ngrok.com roi thay dong tren bang dong duoi:
    REM start "ngrok" cmd /k "ngrok http --url=ten-cua-ban.ngrok-free.app 8000"

)

start "" "http://localhost:8080"

echo.
echo ==================================
echo ALL SERVICES STARTED
echo ==================================
echo phpMyAdmin: http://localhost:8080
echo Database:   db_quan_ly_diem_danh_v2
echo Username:   root
echo Password:   root
echo Demo login: teacher@example.com / password

pause

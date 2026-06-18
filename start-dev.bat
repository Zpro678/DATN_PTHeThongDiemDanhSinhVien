@echo off
setlocal
cd /d "%~dp0"
set "DOCKER_CONFIG=%~dp0storage\framework\docker-cli-clean"
set "DOCKER_HOST=npipe:////./pipe/dockerDesktopLinuxEngine"
if not exist "%DOCKER_CONFIG%" mkdir "%DOCKER_CONFIG%"

echo ==================================
echo STARTING DATN SERVICES
echo ==================================

REM Docker
docker compose up -d
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

    docker compose up -d
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

docker exec attendance_mysql mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS db_quan_ly_diem_danh_v2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if errorlevel 1 (
    echo Could not ensure the MySQL database exists.
    pause
    exit /b 1
)

php artisan migrate --force --no-interaction
if errorlevel 1 (
    echo Database migration failed.
    echo Run php artisan migrate:fresh --seed only when old data may be deleted.
    pause
    exit /b 1
)

php artisan db:seed --force --no-interaction
if errorlevel 1 (
    echo Demo data seeding failed.
    pause
    exit /b 1
)

REM Laravel
start "Laravel" cmd /k "cd /d ""%~dp0"" && php artisan serve"

REM Queue Worker
start "Queue Worker" cmd /k "cd /d ""%~dp0"" && php artisan queue:work redis"

REM Vite
start "Vite" cmd /k "cd /d ""%~dp0"" && npm.cmd run dev -- --host 127.0.0.1 --strictPort"

REM Socket.IO
start "Socket.IO" cmd /k "cd /d ""%~dp0"" && node server.cjs"

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

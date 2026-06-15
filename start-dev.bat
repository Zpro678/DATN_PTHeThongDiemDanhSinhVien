@echo off
cd /d "%~dp0"

echo ==================================
echo STARTING DATN SERVICES
echo ==================================

REM Docker
docker compose up -d mysql redis phpmyadmin

if errorlevel 1 (
    echo.
    echo Docker services failed to start. Please check Docker Desktop and port 8080/3306 availability.
    pause
    exit /b 1
)

timeout /t 5 > nul

REM Laravel
start cmd /k "php artisan serve"

REM Queue Worker
start cmd /k "php artisan queue:work redis"

REM Vite
start cmd /k "npm run dev"

REM Socket.IO
start cmd /k "node server.cjs"

echo.
echo ==================================
echo ALL SERVICES STARTED
echo ==================================
echo Laravel:    http://127.0.0.1:8000
echo phpMyAdmin: http://127.0.0.1:8080
echo MySQL:      127.0.0.1:3306
echo.
echo phpMyAdmin login: root / root
echo Database: db_quan_ly_diem_danh

pause

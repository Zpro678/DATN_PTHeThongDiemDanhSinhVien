@echo off

echo ==================================
echo STARTING DATN SERVICES
echo ==================================

REM Docker
start cmd /k "docker compose up -d"

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

pause

@echo off

echo Stopping Docker...

docker compose down

taskkill /F /IM node.exe
taskkill /F /IM php.exe

echo Done

pause
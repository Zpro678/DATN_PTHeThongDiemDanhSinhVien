@echo off
setlocal
cd /d "%~dp0"
set "DOCKER_CONFIG=%~dp0storage\framework\docker-cli-clean"
set "DOCKER_HOST=npipe:////./pipe/dockerDesktopLinuxEngine"
if not exist "%DOCKER_CONFIG%" mkdir "%DOCKER_CONFIG%"

echo Stopping Docker...

docker compose down

taskkill /F /IM node.exe
taskkill /F /IM php.exe

echo Done

pause

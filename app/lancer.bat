@echo off
rem Lance ObservaStage (double-clic). Docker Desktop doit être installé.
cd /d "%~dp0"

docker info >nul 2>&1
if %errorlevel% equ 0 goto demarrer
echo Demarrage de Docker Desktop...
start "" "C:\Program Files\Docker\Docker\Docker Desktop.exe"
:attente
timeout /t 3 /nobreak >nul
docker info >nul 2>&1
if %errorlevel% neq 0 goto attente

:demarrer
echo Demarrage d'ObservaStage...
docker compose up -d
if %errorlevel% neq 0 (
  echo Echec du demarrage.
  pause
  exit /b 1
)
timeout /t 3 /nobreak >nul
start "" http://localhost:8080

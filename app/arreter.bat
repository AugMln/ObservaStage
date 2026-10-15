@echo off
rem Arrête ObservaStage. Les données importées sont conservées.
cd /d "%~dp0"
docker compose stop
echo ObservaStage est arrete. Les donnees sont conservees.
timeout /t 3 >nul

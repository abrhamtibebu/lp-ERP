@echo off
echo Starting Backend and Frontend...
start "Backend Server" cmd /k "cd /d %~dp0\backend && php artisan serve"
timeout /t 3 /nobreak > nul
start "Frontend Server" cmd /k "cd /d %~dp0\frontend && npm run dev"
echo Backend: http://localhost:8000
echo Frontend: http://localhost:3000
pause


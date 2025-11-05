# Start Both Backend and Frontend (PowerShell)
Write-Host "Starting Backend and Frontend..." -ForegroundColor Green
$scriptPath = Split-Path -Parent $MyInvocation.MyCommand.Path

# Start backend in background
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$scriptPath\backend'; php artisan serve"

# Wait a moment for backend to start
Start-Sleep -Seconds 2

# Start frontend
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$scriptPath\frontend'; npm run dev"

Write-Host "Backend: http://localhost:8000" -ForegroundColor Cyan
Write-Host "Frontend: http://localhost:3000" -ForegroundColor Cyan


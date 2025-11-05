# Backend Start Script (PowerShell)
Write-Host "Starting Laravel Backend Server..." -ForegroundColor Green
$scriptPath = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location "$scriptPath\backend"
php artisan serve


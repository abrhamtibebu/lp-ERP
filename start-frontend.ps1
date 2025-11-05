# Frontend Start Script (PowerShell)
Write-Host "Starting Vue Frontend Server..." -ForegroundColor Green
$scriptPath = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location "$scriptPath\frontend"
npm run dev


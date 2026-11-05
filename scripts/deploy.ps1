# ==============================================================================
# BREW & BEAN - Deployment Script (PowerShell)
# ==============================================================================
$ErrorActionPreference = "Stop"

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "   BREW & BEAN - Deploying Penetration Testing Lab        " -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan

$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location "$ScriptDir\.."

if (-not (Test-Path .env)) {
    Write-Host "[+] Creating .env from template..." -ForegroundColor Yellow
    Copy-Item .env.example .env
}

Write-Host "[+] Building and launching Docker Compose services..." -ForegroundColor Green
docker compose up -d --build

Write-Host "`n[+] Checking container states..." -ForegroundColor Green
docker compose ps

Write-Host "`n==========================================================" -ForegroundColor Cyan
Write-Host "   Deployment Complete!                                   " -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host " - Coffee Shop Web Application: http://localhost:80"
Write-Host " - Challenge Verifier / Instructor Portal: http://localhost:8000"
Write-Host " - To inspect logs: docker compose logs -f"
Write-Host " - To reset lab: powershell -File scripts\reset.ps1"
Write-Host "=========================================================="


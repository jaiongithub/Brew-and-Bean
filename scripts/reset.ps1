# ==============================================================================
# BREW & BEAN - Environment Reset Script (PowerShell)
# ==============================================================================
$ErrorActionPreference = "Stop"

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "   BREW & BEAN - Resetting Lab Environment               " -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan

$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location "$ScriptDir\.."

Write-Host "[+] Tearing down existing containers and volumes..." -ForegroundColor Yellow
docker compose down -v --remove-orphans

Write-Host "[+] Starting fresh container instances..." -ForegroundColor Green
docker compose up -d

Write-Host "`n[+] Resetting flag verifier state..." -ForegroundColor Yellow
Start-Sleep -Seconds 3
try {
    Invoke-RestMethod -Uri "http://localhost:8000/api/reset" -Method Post -ErrorAction SilentlyContinue | Out-Null
} catch {}

Write-Host "`n==========================================================" -ForegroundColor Cyan
Write-Host "   Lab Environment Successfully Reset!                    " -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host " - All accounts restored to initial seed state."
Write-Host " - Root cron maintenance script reinstated."
Write-Host " - Flag challenge logs cleared."
Write-Host "=========================================================="


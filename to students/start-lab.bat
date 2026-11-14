@echo off
title BREW ^& BEAN Cyber Range Launcher
echo ===================================================
echo   BREW ^& BEAN - Penetration Testing Examination
echo ===================================================
echo.
echo Checking for previous lab containers...
docker stop brewandbean >nul 2>&1
docker rm brewandbean >nul 2>&1

echo Starting BREW ^& BEAN container on ports 80 and 8000...
docker run -d --name brewandbean -p 80:80 -p 8000:8000 jaiongithub/brewandbean:latest

if %errorlevel% neq 0 (
    echo.
    echo [!] ERROR: Could not start container. Please ensure Docker Desktop is running.
    echo.
    pause
    exit /b 1
)

echo.
echo ===================================================
echo [+] Lab environment successfully started!
echo [+] 1. Target Web Application : http://localhost
echo [+] 2. Verification Portal    : http://localhost:8000
echo ===================================================
echo.
echo Launching browser interfaces...
timeout /t 2 >nul
start http://localhost:8000
start http://localhost
echo.
echo Keep this window open or press any key to close. (Lab will continue running in background)
pause

@echo off
set REGISTRY_USER=jaiongithub
set IMAGE_NAME=brewandbean
set TAG=latest

echo ===================================================
echo   BREW ^& BEAN - Image Build ^& Registry Push
echo ===================================================
echo.
echo Target Image: %REGISTRY_USER%/%IMAGE_NAME%:%TAG%
echo.

echo 1. Running code obfuscation pipeline...
python "to students\obfuscate.py"

if %errorlevel% neq 0 (
    echo [!] Obfuscation failed!
    pause
    exit /b 1
)

echo.
echo 2. Building Docker image...
docker build -t %REGISTRY_USER%/%IMAGE_NAME%:%TAG% -t %IMAGE_NAME%:latest -f "to students\Dockerfile" .

if %errorlevel% neq 0 (
    echo [!] Docker build failed!
    pause
    exit /b 1
)

echo.
echo 3. Pushing image to Docker Registry (%REGISTRY_USER%/%IMAGE_NAME%:%TAG%)...
docker push %REGISTRY_USER%/%IMAGE_NAME%:%TAG%

if %errorlevel% neq 0 (
    echo [!] Docker push failed. Please ensure you are logged into Docker Hub via 'docker login'.
    pause
    exit /b 1
)

echo.
echo [+] Successfully built and published %REGISTRY_USER%/%IMAGE_NAME%:%TAG%!
pause

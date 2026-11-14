@echo off
echo Stopping and removing BREW ^& BEAN lab container...
docker stop brewandbean
docker rm brewandbean
echo.
echo [+] Lab successfully stopped and cleared.
pause

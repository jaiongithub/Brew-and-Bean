#!/bin/bash
echo "==================================================="
echo "  BREW & BEAN - Penetration Testing Examination"
echo "==================================================="
echo ""
echo "Cleaning up any existing containers..."
docker stop brewandbean >/dev/null 2>&1 || true
docker rm brewandbean >/dev/null 2>&1 || true

echo "Starting BREW & BEAN container on ports 80 and 8000..."
docker run -d --name brewandbean -p 80:80 -p 8000:8000 jaiongithub/brewandbean:latest

if [ $? -ne 0 ]; then
    echo ""
    echo "[!] ERROR: Could not start container. Please ensure Docker daemon is running."
    exit 1
fi

echo ""
echo "==================================================="
echo "[+] Lab environment successfully started!"
echo "[+] 1. Target Web Application : http://localhost"
echo "[+] 2. Verification Portal    : http://localhost:8000"
echo "==================================================="
echo ""

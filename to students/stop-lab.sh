#!/bin/bash
echo "Stopping and removing BREW & BEAN lab container..."
docker stop brewandbean >/dev/null 2>&1 || true
docker rm brewandbean >/dev/null 2>&1 || true
echo "[+] Lab successfully stopped and cleared."

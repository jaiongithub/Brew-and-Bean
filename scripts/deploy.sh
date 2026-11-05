#!/bin/bash
# ==============================================================================
# BREW & BEAN - Deployment Script
# ==============================================================================
set -e

echo "=========================================================="
echo "   BREW & BEAN - Deploying Penetration Testing Lab        "
echo "=========================================================="

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$SCRIPT_DIR"

if [ ! -f .env ]; then
    echo "[+] Creating .env from template..."
    cp .env.example .env
fi

echo "[+] Building and launching Docker Compose services..."
docker compose up -d --build

echo ""
echo "[+] Waiting for database and web containers to be healthy..."
docker compose ps

echo ""
echo "=========================================================="
echo "   Deployment Complete!                                   "
echo "=========================================================="
echo " - Coffee Shop Web Application: http://localhost:80"
echo " - Challenge Verifier / Instructor Portal: http://localhost:8000"
echo " - To inspect logs: docker compose logs -f"
echo " - To reset lab: ./scripts/reset.sh"
echo "=========================================================="


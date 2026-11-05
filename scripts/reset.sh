#!/bin/bash
# ==============================================================================
# BREW & BEAN - Environment Reset Script
# Restores database, flags, cache, and services to pristine vulnerable state
# ==============================================================================
set -e

echo "=========================================================="
echo "   BREW & BEAN - Resetting Lab Environment               "
echo "=========================================================="

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$SCRIPT_DIR"

echo "[+] Tearing down existing containers and volumes..."
docker compose down -v --remove-orphans

echo "[+] Starting fresh container instances..."
docker compose up -d

echo ""
echo "[+] Resetting flag verifier state..."
sleep 3
curl -s -X POST http://localhost:8000/api/reset || true

echo ""
echo "=========================================================="
echo "   Lab Environment Successfully Reset!                    "
echo "=========================================================="
echo " - All accounts restored to initial seed state."
echo " - Root cron maintenance script reinstated."
echo " - Flag challenge logs cleared."
echo "=========================================================="


#!/bin/bash
# ==============================================================================
# BREW & BEAN System Maintenance and Cache Housekeeping Service
# Scheduled via root crontab to run every minute
# ==============================================================================
CACHE_DIR="/var/www/html/tmp/cache"
LOG_FILE="/var/log/brew-maintenance.log"

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Starting routine cache cleanup..." >> "$LOG_FILE"
if [ -d "$CACHE_DIR" ]; then
    find "$CACHE_DIR" -type f -mmin +60 -delete 2>/dev/null
fi
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Maintenance cycle complete." >> "$LOG_FILE"


#!/bin/bash
set -e

echo "[+] Initializing BREW & BEAN web environment..."

# Setup flag directories and permissions
mkdir -p /opt/flags /root/flags /var/log /opt/scripts /var/www/html/tmp/cache

# Write Flag 4 (OS Command Injection initial access proof file)
FLAG_F04_VAL="${FLAG_F04:-FLAG{BREW_COMMAND_INJECTION_04}}"
echo "$FLAG_F04_VAL" > /opt/flags/flag4.txt
chown root:www-data /opt/flags/flag4.txt
chmod 0644 /opt/flags/flag4.txt

# Write Flag 6 (Root privilege escalation target)
FLAG_F06_VAL="${FLAG_F06:-FLAG{BREW_ROOT_ESCALATION_06}}"
echo "$FLAG_F06_VAL" > /root/flags/root.txt
chown root:root /root/flags/root.txt
chmod 0600 /root/flags/root.txt
chmod 0700 /root/flags

# Ensure maintenance script is writable by www-data (Privilege Escalation Vector)
cp -f /cron/cleanup_cache.sh /opt/scripts/cleanup_cache.sh
chown root:www-data /opt/scripts/cleanup_cache.sh
chmod 0777 /opt/scripts/cleanup_cache.sh

# Install crontab
cp -f /cron/brew-cron /etc/cron.d/brew-cron
chmod 0644 /etc/cron.d/brew-cron
touch /var/log/brew-maintenance.log
chmod 0666 /var/log/brew-maintenance.log

# Ensure default Apache index.html is removed so index.php is loaded
rm -f /var/www/html/index.html

# Ensure web permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod -R 777 /var/www/html/tmp/cache

# Start cron daemon
echo "[+] Starting cron service..."
cron

# Wait for database if needed
echo "[+] Database host: ${DB_HOST:-brew-db}"

# Start Apache in foreground
echo "[+] Starting Apache HTTP Server on port 80..."
exec apache2ctl -D FOREGROUND


#!/bin/bash
set -e

echo "[+] Initializing BREW & BEAN Unified Cyber Range Container..."

# Ensure internal hostname brew-verifier resolves locally for internal hooks
if ! grep -q "brew-verifier" /etc/hosts 2>/dev/null; then
    echo "127.0.0.1 brew-verifier" >> /etc/hosts 2>/dev/null || true
fi

# Setup flag directories and permissions
mkdir -p /opt/flags /root/flags /var/log /opt/scripts /var/www/html/tmp/cache /var/log/supervisor /var/run/mysqld
chown -R mysql:mysql /var/run/mysqld /var/lib/mysql

# Write Flag 4 (OS Command Injection initial access proof file)
if [ -n "$FLAG_F04" ]; then
    FLAG_F04_VAL="$FLAG_F04"
else
    FLAG_F04_VAL="FLAG{BREW_COMMAND_INJECTION_04}"
fi
printf "%s\n" "$FLAG_F04_VAL" > /opt/flags/flag4.txt
chown root:www-data /opt/flags/flag4.txt
chmod 0644 /opt/flags/flag4.txt

# Write Flag 6 (Root privilege escalation target)
if [ -n "$FLAG_F06" ]; then
    FLAG_F06_VAL="$FLAG_F06"
else
    FLAG_F06_VAL="FLAG{BREW_ROOT_ESCALATION_06}"
fi
printf "%s\n" "$FLAG_F06_VAL" > /root/flags/root.txt
chown root:root /root/flags/root.txt
chmod 0600 /root/flags/root.txt
chmod 0700 /root/flags

# Ensure maintenance script is writable by www-data (Privilege Escalation Vector)
if [ -f /cron/cleanup_cache.sh ]; then
    cp -f /cron/cleanup_cache.sh /opt/scripts/cleanup_cache.sh
fi
chown root:www-data /opt/scripts/cleanup_cache.sh
chmod 0777 /opt/scripts/cleanup_cache.sh

# Install crontab
if [ -f /cron/brew-cron ]; then
    cp -f /cron/brew-cron /etc/cron.d/brew-cron
    chmod 0644 /etc/cron.d/brew-cron
fi
touch /var/log/brew-maintenance.log
chmod 0666 /var/log/brew-maintenance.log

# Ensure web permissions
rm -f /var/www/html/index.html
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod -R 777 /var/www/html/tmp/cache

# Enforce strict permission lockdown on internal verifier and bot
chown -R root:root /opt/verifier /opt/staff-bot
chmod -R 700 /opt/verifier /opt/staff-bot

echo "[+] Starting supervisor process manager..."
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf


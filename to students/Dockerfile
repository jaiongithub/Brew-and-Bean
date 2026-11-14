FROM mcr.microsoft.com/playwright:v1.42.1-jammy

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC

# Install MariaDB, Apache, PHP, Python, Supervisor, and tools
RUN apt-get update && apt-get install -y --no-install-recommends \
    mariadb-server \
    mariadb-client \
    apache2 \
    php \
    php-mysql \
    php-curl \
    php-mbstring \
    php-xml \
    php-zip \
    libapache2-mod-php \
    python3 \
    python3-pip \
    supervisor \
    cron \
    iputils-ping \
    curl \
    net-tools \
    procps \
    ca-certificates \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Install Python requirements for Verifier Portal
RUN pip3 install --no-cache-dir Flask==3.0.2 Werkzeug==3.0.1

# Configure Apache
COPY web/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Pre-seed MariaDB Database during build and delete init.sql
COPY db/init.sql /tmp/init.sql
RUN service mariadb start && \
    mariadb -e "CREATE DATABASE IF NOT EXISTS brewandbean CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" && \
    mariadb -e "CREATE USER IF NOT EXISTS 'brewuser'@'%' IDENTIFIED BY 'BrewBeanSecurePass2026!';" && \
    mariadb -e "CREATE USER IF NOT EXISTS 'brewuser'@'localhost' IDENTIFIED BY 'BrewBeanSecurePass2026!';" && \
    mariadb -e "GRANT ALL PRIVILEGES ON brewandbean.* TO 'brewuser'@'%'; GRANT ALL PRIVILEGES ON brewandbean.* TO 'brewuser'@'localhost'; FLUSH PRIVILEGES;" && \
    mariadb -u root brewandbean < /tmp/init.sql && \
    rm -f /tmp/init.sql && \
    service mariadb stop

# Deploy Obfuscated Web Application
RUN rm -f /var/www/html/index.html
COPY ["to students/dist/web/", "/var/www/html/"]

# Deploy Cron Vector
COPY web/cron/ /cron/

# Deploy Obfuscated Staff Simulation Bot
WORKDIR /opt/staff-bot
COPY ["to students/dist/staff-bot/package.json", "./"]
RUN npm install --omit=dev
COPY ["to students/dist/staff-bot/bot.js", "./"]

# Deploy Obfuscated Python Verifier
WORKDIR /opt/verifier
COPY ["to students/dist/verifier/app.py", "./"]
COPY ["to students/dist/verifier/templates/", "./templates/"]
RUN python3 -m compileall -b . && rm -f app.py

# Configure Supervisor and Entrypoint
COPY ["to students/supervisord.conf", "/etc/supervisor/conf.d/supervisord.conf"]
COPY ["to students/entrypoint.sh", "/entrypoint.sh"]
RUN chmod +x /entrypoint.sh /cron/cleanup_cache.sh

# Environment Defaults
ENV DB_HOST=127.0.0.1
ENV DB_PORT=3306
ENV DB_NAME=brewandbean
ENV DB_USER=brewuser
ENV DB_PASSWORD=BrewBeanSecurePass2026!
ENV WEB_INTERNAL_URL=http://127.0.0.1
ENV VERIFIER_INTERNAL_URL=http://127.0.0.1:8000
ENV NODE_ENV=production

EXPOSE 80 8000

WORKDIR /

ENTRYPOINT ["/entrypoint.sh"]

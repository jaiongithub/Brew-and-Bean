FROM ubuntu:24.04

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC

# Install Apache, PHP 8.3, and standard Linux utilities
RUN apt-get update && apt-get install -y --no-install-recommends \
    apache2 \
    php \
    php-mysql \
    php-curl \
    php-mbstring \
    php-xml \
    php-zip \
    libapache2-mod-php \
    cron \
    iputils-ping \
    curl \
    net-tools \
    procps \
    vim-tiny \
    ca-certificates \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Apache configuration
COPY apache-vhost.conf /etc/apache2/sites-available/000-default.conf
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Application and cron files
RUN rm -f /var/www/html/index.html
COPY src/ /var/www/html/
COPY cron/ /cron/
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh /cron/cleanup_cache.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]


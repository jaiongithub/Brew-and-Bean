FROM mariadb:10.11

# Add initialization scripts
COPY init.sql /docker-entrypoint-initdb.d/01-init.sql

# Basic healthcheck configuration
HEALTHCHECK --interval=5s --timeout=5s --retries=10 \
  CMD mariadb-admin ping -h localhost -u root -p${MARIADB_ROOT_PASSWORD} || exit 1


#!/bin/sh
set -e

# Support CA certificate passed via environment variable if provided
if [ -n "$AIVEN_CA_CERT" ]; then
    echo "$AIVEN_CA_CERT" > /usr/local/share/ca-certificates/aiven-ca.crt
    update-ca-certificates
fi

# Ensure MYSQL_ATTR_SSL_CA points to the certificate if available
if [ -f "/usr/local/share/ca-certificates/aiven-ca.crt" ]; then
    export MYSQL_ATTR_SSL_CA="/usr/local/share/ca-certificates/aiven-ca.crt"
elif [ -f "/var/www/html/docker/aiven-ca.pem" ]; then
    export MYSQL_ATTR_SSL_CA="/var/www/html/docker/aiven-ca.pem"
fi

export MYSQL_ATTR_SSL_VERIFY_SERVER_CERT="${MYSQL_ATTR_SSL_VERIFY_SERVER_CERT:-false}"

php artisan optimize
php artisan migrate --force
php artisan db:seed --force

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
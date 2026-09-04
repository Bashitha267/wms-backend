#!/bin/sh
set -e

export MYSQL_ATTR_SSL_CA=/usr/local/share/ca-certificates/aiven-ca.crt

php artisan optimize
php artisan migrate --force
php artisan db:seed --force

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
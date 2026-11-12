#!/bin/sh
set -eu

cd /var/www/html

mkdir -p \
    bootstrap/cache \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs
chown -R www-data:www-data storage bootstrap/cache

# Wait for a managed database to accept connections on first boot.
attempt=0
until php artisan migrate --force --no-interaction; do
    attempt=$((attempt + 1))
    if [ "$attempt" -ge 10 ]; then
        echo "Database did not become ready after 10 attempts." >&2
        exit 1
    fi
    echo "Database not ready (attempt $attempt/10); retrying in 3s..." >&2
    sleep 3
done

if [ "${SEED_DATABASE:-false}" = "true" ]; then
    php artisan db:seed --force --no-interaction
fi

php artisan optimize

exec "$@"

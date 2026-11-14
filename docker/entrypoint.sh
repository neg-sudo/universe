#!/bin/sh
set -eu

cd /var/www/html

# Render (and most PaaS providers) set PORT. Bind Apache to it when present.
PORT="${PORT:-80}"
echo "Listen ${PORT}" > /etc/apache2/ports.conf
sed "s/__PORT__/${PORT}/g" /etc/apache2/templates/apache.conf \
    > /etc/apache2/sites-available/000-default.conf

# Create a runtime .env when the image is started without one.
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Render exposes the public URL; use it unless APP_URL is set explicitly.
# This keeps asset links, redirects and cookies on the right host.
if [ -z "${APP_URL:-}" ] && [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
    export APP_URL="$RENDER_EXTERNAL_URL"
fi

# Render's generated APP_KEY is a 256-bit base64 string, not Laravel's
# "base64:..." form. Derive a stable 32-byte key from it so sessions survive
# restarts. A key supplied as base64:... or as exactly 32 bytes is kept as is.
case "${APP_KEY:-}" in
    "")
        ;;
    base64:*)
        ;;
    *)
        if [ "${#APP_KEY}" -ne 32 ]; then
            APP_KEY="base64:$(APP_KEY="$APP_KEY" php -r 'echo base64_encode(hash("sha256", getenv("APP_KEY"), true));')"
            export APP_KEY
        fi
        ;;
esac

# Generate a valid Laravel key if the platform did not provide one.
# Set APP_KEY in the dashboard for stable sessions across restarts.
if [ -z "${APP_KEY:-}" ] && ! grep -qE '^APP_KEY=base64:.+' .env; then
    php artisan key:generate --force --no-interaction
fi

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

# Ensure Apache (www-data) can write logs, caches and a local SQLite file.
chown -R www-data:www-data storage bootstrap/cache database

exec "$@"

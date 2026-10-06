#!/bin/sh
set -e

cd /var/www/html

# Make sure the storage tree exists (a fresh volume may be empty)
mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions \
         storage/framework/views storage/logs bootstrap/cache

# APP_KEY: use the one provided, otherwise generate one once and keep it in the storage volume
if [ -z "$APP_KEY" ]; then
    if [ ! -s storage/app/.app_key ]; then
        echo "[entrypoint] No APP_KEY set, generating one (stored in storage/app/.app_key)"
        php -r 'echo "base64:".base64_encode(random_bytes(32));' > storage/app/.app_key
    fi
    APP_KEY="$(cat storage/app/.app_key)"
    export APP_KEY
fi

# Wait for the database
if [ "${DB_CONNECTION:-mysql}" = "mysql" ]; then
    echo "[entrypoint] Waiting for database at ${DB_HOST:-mysql}:${DB_PORT:-3306}..."
    tries=0
    until php -r '
        try {
            new PDO(
                "mysql:host=".getenv("DB_HOST").";port=".(getenv("DB_PORT") ?: 3306).";dbname=".getenv("DB_DATABASE"),
                getenv("DB_USERNAME"), getenv("DB_PASSWORD")
            );
        } catch (Throwable $e) { exit(1); }
    ' 2>/dev/null; do
        tries=$((tries + 1))
        if [ "$tries" -ge 60 ]; then
            echo "[entrypoint] Database not reachable after 120s, giving up." >&2
            exit 1
        fi
        sleep 2
    done
    echo "[entrypoint] Database is up."
fi

php artisan config:clear >/dev/null

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

# Seed demo data only once (the seeders are not idempotent)
if [ "${SEED_DATABASE:-false}" = "true" ] && [ ! -f storage/app/.seeded ]; then
    echo "[entrypoint] Seeding database..."
    php artisan db:seed --force
    touch storage/app/.seeded
fi

if [ ! -L public/storage ]; then
    php artisan storage:link >/dev/null 2>&1 || true
fi

if [ "${APP_ENV:-production}" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    # Non-fatal: a single broken template should not stop the container; views also compile on demand
    php artisan view:cache || echo "[entrypoint] WARNING: view:cache failed, views will be compiled on demand" >&2
fi

chown -R www-data:www-data storage bootstrap/cache

exec "$@"

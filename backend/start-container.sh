#!/bin/bash
set -e

cd /app

# Run Laravel setup
php artisan storage:link --force 2>/dev/null || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
if [ "${RAILPACK_SKIP_MIGRATIONS}" != "true" ]; then
    php artisan migrate --force
fi

# Start FrankenPHP
exec frankenphp run --config /app/Caddyfile

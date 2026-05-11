#!/bin/bash
set -e

cd /app

# Ensure storage directories exist
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Set permissions
chmod -R 775 storage bootstrap/cache

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

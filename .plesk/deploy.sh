#!/bin/bash
# ==============================================================
# Post-Deploy Script - MLM NEXUS (system.nexuscommunity.id)
# Dijalankan otomatis oleh Plesk setelah git pull
# ==============================================================

set -e

# Path aplikasi di server (sesuaikan jika berbeda)
APP_DIR=$(dirname "$(dirname "$(realpath "$0")")")

echo ">>> [1/7] Install Composer dependencies (production)..."
composer install --no-dev --optimize-autoloader --no-interaction

echo ">>> [2/7] Copy .env jika belum ada..."
if [ ! -f "$APP_DIR/.env" ]; then
    cp "$APP_DIR/.env.example" "$APP_DIR/.env"
    echo "  .env dibuat dari .env.example - HARAP ISI MANUAL!"
fi

echo ">>> [3/7] Generate app key jika belum ada..."
php artisan key:generate --no-interaction 2>/dev/null || true

echo ">>> [4/7] Run database migrations (safe - tidak drop data)..."
php artisan migrate --force --no-interaction

echo ">>> [5/7] Clear & cache config/route/view..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo ">>> [6/7] Install & build NPM assets..."
if [ -f "package.json" ]; then
    npm ci --prefer-offline 2>/dev/null || npm install
    npm run build
fi

echo ">>> [7/7] Set storage permissions..."
chmod -R 775 storage bootstrap/cache
chown -R $(whoami):psaserv storage bootstrap/cache 2>/dev/null || true
php artisan storage:link --force 2>/dev/null || true

echo ""
echo "✅ Deploy selesai! MLM NEXUS siap di system.nexuscommunity.id"

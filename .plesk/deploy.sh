#!/bin/bash
# ==============================================================
# Post-Deploy Script - MLM NEXUS (system.nexuscommunity.id)
# Dijalankan otomatis oleh Plesk setelah git pull
# ==============================================================

export PATH=$PATH:/usr/local/bin:/usr/bin:/bin:/usr/local/sbin:/usr/sbin

if command -v php >/dev/null 2>&1; then
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

echo "✅ Deploy selesai! MLM NEXUS siap di system.nexuscommunity.id"

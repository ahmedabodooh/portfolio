#!/usr/bin/env bash
# Run this from the project root on the server after a git push.
# Usage:  cd ~/ahmed-abo-dooh.site && bash deploy.sh

set -e

echo "==> 1/5  Syncing from GitHub (discards local conflicts)…"
git fetch origin main
git reset --hard origin/main

echo "==> 2/5  Installing composer dependencies…"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> 3/5  Ensuring storage directories exist…"
mkdir -p storage/framework/{cache/data,sessions,testing,views}
mkdir -p storage/app/{public,private} storage/logs
chmod -R 775 storage bootstrap/cache

echo "==> 4/5  Refreshing the public/storage symlink…"
rm -f public/storage
php artisan storage:link

echo "==> 5/5  Clearing cached config/routes/views…"
php artisan config:clear
php artisan cache:clear
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

echo ""
echo "✅ Deploy finished."
echo "   Next manual steps (only the first time):"
echo "   - Upload storage/app/public image folders via cPanel File Manager."
echo "   - Verify .env has correct DB_USERNAME, quoted DB_PASSWORD,"
echo "     and SESSION_DOMAIN=ahmed-abo-dooh.site (no leading dot)."

#!/bin/bash
# Deploy script for AKL Logistik (production)
# Usage: ./deploy.sh [branch]
set -e

BRANCH="${1:-main}"
APP_DIR="/var/www/akl-logistik"
PHP="/usr/bin/php"
COMPOSER="/usr/local/bin/composer"

echo "=== AKL Logistik Deploy ==="
echo "Branch: $BRANCH"
echo "Dir: $APP_DIR"

cd "$APP_DIR"

echo "[1/6] Pull latest code..."
git fetch origin
git reset --hard "origin/$BRANCH"

echo "[2/6] Install dependencies..."
$COMPOSER install --no-dev --optimize-autoloader --no-interaction

echo "[3/6] Run migrations..."
$PHP artisan migrate --force

echo "[4/6] Clear & cache..."
$PHP artisan config:cache
$PHP artisan route:cache
$PHP artisan view:cache
$PHP artisan cache:clear

echo "[5/6] Storage link..."
$PHP artisan storage:link 2>/dev/null || true

echo "[6/6] Set permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "=== Deploy complete ==="

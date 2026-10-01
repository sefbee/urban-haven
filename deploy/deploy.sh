#!/usr/bin/env bash
# Zero-surprise deploy for a single server. Run as the deploy user from the app directory.
# Usage: deploy/deploy.sh [git-ref]
set -euo pipefail

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"
REF="${1:-main}"
cd "$APP_DIR"

php artisan down --retry=30 --render="errors::503" || true
trap 'php artisan up' EXIT

git fetch --prune origin
git checkout --force "$REF"
git reset --hard "origin/$REF" 2>/dev/null || true

composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
npm ci
npm run build
rm -f public/hot

deploy/backup.sh "$APP_DIR"
php artisan migrate --force --no-interaction
php artisan db:seed --class=RolesPermissionsSeeder --force --no-interaction
php artisan db:seed --class=ReferenceDataSeeder --force --no-interaction

php artisan storage:link || true
php artisan optimize:clear
php artisan optimize
php artisan view:cache
php artisan queue:restart

php artisan uh:health

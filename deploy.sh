#!/bin/bash

set -euo pipefail

echo "=========================================="
echo "🚀 URBAN HAVEN DEPLOYMENT PACKAGER"
echo "=========================================="

echo "[1/4] Optimizing local configuration..."
# php artisan optimize:clear

echo "[2/4] Building frontend assets (Vite)..."
npm run build

echo "[3/4] Packaging files into deploy.zip..."
# Remove old zip if it exists
rm -f deploy.zip

# Package only the Laravel application and its runtime dependencies. This avoids
# bundling local uploads, editor worktrees, scratch files, and previous archives.
zip -r -q deploy.zip \
    app bootstrap config database public resources routes storage vendor \
    artisan composer.json composer.lock package.json package-lock.json vite.config.js .npmrc \
    -x 'storage/app/*' \
    -x 'storage/framework/cache/*' \
    -x 'storage/framework/sessions/*' \
    -x 'storage/framework/views/*' \
    -x 'storage/logs/*' \
    -x 'public/hot' \
    -x 'public/storage'

FILESIZE=$(stat -c%s "deploy.zip")
echo "[4/4] Package created successfully! (Size: $(($FILESIZE/1024/1024)) MB)"
echo "=========================================="
echo "✅ DEPLOYMENT PACKAGE READY: deploy.zip"
echo "=========================================="

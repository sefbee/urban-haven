#!/bin/bash

# Exit script on any error
set -e

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

# Zip everything, excluding unnecessary dev/local files to save space
zip -r -q deploy.zip . \
    -x "*.git*" \
    -x "*node_modules*" \
    -x "*storage/logs/*.log" \
    -x "*storage/framework/cache/data/*" \
    -x "*storage/framework/views/*" \
    -x "*tests*" \
    -x "*.env" -x "public/hot" \
    -x "deploy.sh" \
    -x "deploy.zip"

FILESIZE=$(stat -c%s "deploy.zip")
echo "[4/4] Package created successfully! (Size: $(($FILESIZE/1024/1024)) MB)"
echo "=========================================="
echo "✅ DEPLOYMENT PACKAGE READY: deploy.zip"
echo "=========================================="

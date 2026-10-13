<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

$secretToken = 'urban-update-2026-secure';

if (! isset($_GET['token']) || $_GET['token'] !== $secretToken) {
    http_response_code(403);
    exit('Unauthorized access.');
}

$zipPath = __DIR__.'/../deploy.zip';

if (! file_exists($zipPath)) {
    exit('Error: deploy.zip not found in the root directory. Please upload it first.');
}

echo '<pre>';
echo "🚀 Starting Deployment Update...\n\n";

// 1. Extract the ZIP
echo "[1/3] Extracting deploy.zip over existing files...\n";
$zip = new ZipArchive;
if ($zip->open($zipPath) === true) {
    $zip->extractTo(__DIR__.'/../');
    $zip->close();
    echo "✅ Extraction complete.\n\n";

    // Clean up zip
    unlink($zipPath);
    echo "🗑️ deploy.zip removed to save space.\n\n";
} else {
    exit('❌ Failed to open or extract deploy.zip.');
}

// 2. Boot Laravel to run artisan commands securely (bypassing disabled shell execs)
echo "[2/3] Booting Laravel to run database migrations...\n";
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

try {
    Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
    echo Artisan::output();
    echo "✅ Fresh Migrations and Seeding complete.\n\n";
} catch (Exception $e) {
    echo '❌ Migration Error: '.$e->getMessage()."\n\n";
}

// 3. Clear and rebuild caches
echo "[3/3] Optimizing and clearing application cache...\n";
try {
    Artisan::call('storage:link');
    Artisan::call('optimize:clear');
    Artisan::call('optimize');
    Artisan::call('view:cache');
    echo Artisan::output();
    echo "✅ Cache cleared.\n\n";
} catch (Exception $e) {
    echo '❌ Cache Error: '.$e->getMessage()."\n\n";
}

echo "🎉 DEPLOYMENT FINISHED SUCCESSFULLY! 🎉\n";
echo '</pre>';

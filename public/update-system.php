<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

$secretToken = 'urban-update-2026-secure';

if (! isset($_GET['token']) || $_GET['token'] !== $secretToken) {
    http_response_code(403);
    exit('Unauthorized access.');
}

$publicZipPath = __DIR__.'/deploy.zip';
$rootZipPath = __DIR__.'/../deploy.zip';
$zipPath = file_exists($publicZipPath) ? $publicZipPath : $rootZipPath;
$applicationPath = __DIR__.'/..';

if (! file_exists($zipPath)) {
    http_response_code(404);
    exit('Error: deploy.zip not found. Upload it to public_html first.');
}

echo '<pre>';
echo "🚀 Starting Deployment Update...\n\n";

// 1. Extract the ZIP
echo "[1/3] Extracting deploy.zip over existing files...\n";
$zip = new ZipArchive;
if ($zip->open($zipPath) === true) {
    $zip->extractTo($applicationPath);
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
    $migrationExitCode = Artisan::call('migrate', ['--force' => true, '--no-interaction' => true]);
    echo Artisan::output();

    if ($migrationExitCode !== 0) {
        http_response_code(500);
        exit("❌ Migration failed. Deployment stopped before cache optimization.\n</pre>");
    }

    echo "✅ Migrations complete.\n\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo Artisan::output();
    echo '❌ Migration Error: '.$e->getMessage()."\n\n";
    exit("Deployment stopped before cache optimization.\n</pre>");
}

// 3. Clear and rebuild caches
echo "[3/3] Optimizing and clearing application cache...\n";
try {
    foreach (['storage:link', 'optimize:clear', 'optimize', 'view:cache'] as $command) {
        $commandExitCode = Artisan::call($command);
        echo Artisan::output();

        if ($commandExitCode !== 0) {
            throw new RuntimeException("Artisan command [{$command}] failed with exit code {$commandExitCode}.");
        }
    }

    echo "✅ Cache cleared.\n\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo '❌ Cache Error: '.$e->getMessage()."\n\n";
    exit("Deployment stopped during cache optimization.\n</pre>");
}

echo "🎉 DEPLOYMENT FINISHED SUCCESSFULLY! 🎉\n";
echo '</pre>';

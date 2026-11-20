<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// ============================================================================
// SELF-HEALING & ZERO-CONFIG DEPLOYMENT CHECK (cPanel / Hostinger / VPS)
// ============================================================================
$baseDir = dirname(__DIR__);

// 1. Ensure .env exists (copy from .env.example if missing)
if (!file_exists($baseDir . '/.env') && file_exists($baseDir . '/.env.example')) {
    @copy($baseDir . '/.env.example', $baseDir . '/.env');
}

// 2. Ensure SQLite database file exists and is populated
$sqlitePath = $baseDir . '/database/database.sqlite';
$sqliteDefault = $baseDir . '/database/database.sqlite.default';
if (!file_exists($sqlitePath) || filesize($sqlitePath) === 0) {
    if (file_exists($sqliteDefault) && filesize($sqliteDefault) > 0) {
        @copy($sqliteDefault, $sqlitePath);
    } else {
        @touch($sqlitePath);
    }
}

// 3. Ensure essential storage and cache directories exist with write permissions
$requiredDirs = [
    $baseDir . '/storage/app/public/payment_receipts',
    $baseDir . '/storage/framework/cache/data',
    $baseDir . '/storage/framework/sessions',
    $baseDir . '/storage/framework/views',
    $baseDir . '/storage/logs',
    $baseDir . '/bootstrap/cache',
];
foreach ($requiredDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader (or fallback to static storefront if vendor/ is not installed yet)...
if (!file_exists(__DIR__.'/../vendor/autoload.php')) {
    require __DIR__.'/../index.php';
    exit;
}

require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());

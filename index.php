<?php
/**
 * TOUCHSTAINS — Root Entry Point for Hostinger / cPanel / Shared Hosting & Laravel
 * Automatically serves Laravel when vendor/autoload.php is installed,
 * or falls back gracefully to static HTML storefront when deployed without vendor/.
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/'
);

// 1. If a static file exists in public/, let it be served when using PHP built-in server
if ($uri !== '/' && PHP_SAPI === 'cli-server') {
    if (file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
        return false;
    }
    if (file_exists(__DIR__ . '/public' . $uri) && !is_dir(__DIR__ . '/public' . $uri)) {
        return false;
    }
}

// 2. If Composer vendor/autoload.php is present, run the full Laravel + SQLite application
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/public/index.php';
    exit;
}

// 3. Zero-dependency fallback for Git deployments where `composer install` hasn't run yet
$cleanPath = trim($uri, '/');

if ($cleanPath === '' || $cleanPath === 'index.html' || $cleanPath === 'index.php') {
    if (file_exists(__DIR__ . '/index.html')) {
        header('Content-Type: text/html; charset=UTF-8');
        readfile(__DIR__ . '/index.html');
        exit;
    }
}

// Check if it's a direct page route (e.g. /checkout, /shop, /pages/checkout.html)
$pageName = preg_replace('#^pages/#', '', $cleanPath);
$pageName = preg_replace('#\.html$#', '', $pageName);

$pageFile = __DIR__ . '/pages/' . $pageName . '.html';
if (file_exists($pageFile)) {
    header('Content-Type: text/html; charset=UTF-8');
    $html = file_get_contents($pageFile);
    // If accessed at root-level clean URL (e.g., /checkout instead of /pages/checkout.html),
    // rewrite ../ relative asset paths to root-relative paths
    if (!str_starts_with($cleanPath, 'pages/')) {
        $html = str_replace(['"../', "'../"], ['"/', "'/"], $html);
    }
    echo $html;
    exit;
}

// Fallback to index.html
if (file_exists(__DIR__ . '/index.html')) {
    header('Content-Type: text/html; charset=UTF-8');
    readfile(__DIR__ . '/index.html');
    exit;
}

http_response_code(404);
echo '404 Not Found';

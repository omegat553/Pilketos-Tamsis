<?php
declare(strict_types=1);

// Serve public assets from the read-only deployment bundle. All other paths
// are handled by the application router below, so PHP source is never public.
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (str_starts_with($path, '/assets/')) {
    $assetRoot = realpath(__DIR__ . '/../assets');
    $assetFile = realpath(__DIR__ . '/..' . $path);

    if ($assetRoot !== false && $assetFile !== false && str_starts_with($assetFile, $assetRoot . DIRECTORY_SEPARATOR) && is_file($assetFile)) {
        $mimeTypes = [
            'css' => 'text/css; charset=UTF-8',
            'js' => 'application/javascript; charset=UTF-8',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
        ];
        $extension = strtolower(pathinfo($assetFile, PATHINFO_EXTENSION));
        $mimeType = $mimeTypes[$extension] ?? mime_content_type($assetFile) ?: 'application/octet-stream';
        header('Content-Type: ' . $mimeType);
        header('Cache-Control: public, max-age=3600');
        readfile($assetFile);
        exit;
    }

    http_response_code(404);
    exit;
}

// The original request URI remains available after Vercel rewrites it here.
require_once __DIR__ . '/../index.php';

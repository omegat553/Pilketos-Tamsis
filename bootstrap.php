<?php
declare(strict_types=1);

// Autoloader PSR-4 sederhana
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Load konfigurasi aplikasi
require_once __DIR__ . '/config/app.php';

// Inisialisasi Sesi secara aman
\App\Core\Session::start();

// Kirim HTTP Security Headers
\App\Core\Security::sendHeaders();

// Global Helper Functions untuk Views
if (!function_exists('e')) {
    function e(?string $value): string
    {
        return \App\Core\Security::escape($value);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return \App\Core\Security::csrfToken();
    }
}

if (!function_exists('page_title')) {
    function page_title(string $title): string
    {
        return e($title . ' - ' . (defined('APP_NAME') ? APP_NAME : 'E-Voting OSIS'));
    }
}

if (!function_exists('get_logo_url')) {
    function get_logo_url(int $num, string $default): string
    {
        $path = "assets/uploads/logo_{$num}.png";
        $file = __DIR__ . '/' . $path;
        return file_exists($file) ? '/' . $path . '?v=' . filemtime($file) : $default;
    }
}

if (!function_exists('get_favicon_url')) {
    function get_favicon_url(string $default): string
    {
        $path = "assets/uploads/favicon.png";
        $file = __DIR__ . '/' . $path;
        return file_exists($file) ? '/' . $path . '?v=' . filemtime($file) : $default;
    }
}
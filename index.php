<?php
declare(strict_types=1);

// Built-in PHP server static file routing fallback
if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $normalizedPath = '/' . trim($path, '/');

    // Protect internal directories from direct access in CLI server
    if (preg_match('/^\/(app|config|database)\//i', $normalizedPath)) {
        http_response_code(403);
        exit('Access Denied.');
    }

    if (file_exists(__DIR__ . $path) && is_file(__DIR__ . $path)) {
        return false;
    }
}

// Bootstrap application
require_once __DIR__ . '/bootstrap.php';

// Parse path info
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH);
$path = '/' . trim($path, '/');

// Normalize legacy .php extensions to clean URLs (301 redirect)
if (str_ends_with($path, '.php')) {
    $cleanPath = substr($path, 0, -4);
    header("Location: {$cleanPath}", true, 301);
    exit;
}

try {
    // Router logic
    switch ($path) {
        case '/':
            if (\App\Core\Session::has('user_id') && \App\Core\Session::has('user_username')) {
                header('Location: /vote');
            } elseif (\App\Core\Session::has('admin_id') && \App\Core\Session::has('admin_username')) {
                header('Location: /admin');
            } else {
                header('Location: /login');
            }
            exit;

        case '/login':
            (new \App\Controllers\AuthController())->studentLogin();
            break;

        case '/logout':
            (new \App\Controllers\AuthController())->studentLogout();
            break;

        case '/vote':
            (new \App\Controllers\VoteController())->index();
            break;

        case '/thanks':
            (new \App\Controllers\VoteController())->thanks();
            break;

        case '/install':
            (new \App\Controllers\InstallController())->index();
            break;

        case '/admin':
            (new \App\Controllers\AdminController())->dashboard();
            break;

        case '/admin/login':
            (new \App\Controllers\AuthController())->adminLogin();
            break;

        case '/admin/account':
            (new \App\Controllers\AdminController())->changePassword();
            break;

        case '/admin/settings/logos':
            (new \App\Controllers\AdminController())->uploadLogos();
            break;

        case '/admin/logout':
            (new \App\Controllers\AuthController())->adminLogout();
            break;

        case '/admin/candidate/edit':
            (new \App\Controllers\AdminController())->editCandidate();
            break;

        case '/admin/codes/list':
            (new \App\Controllers\AdminController())->listCodes();
            break;

        case '/admin/codes/generate':
            (new \App\Controllers\AdminController())->generateCodes();
            break;

        case '/admin/codes/import':
            (new \App\Controllers\AdminController())->importCodes();
            break;

        case '/admin/codes/export':
            (new \App\Controllers\AdminController())->exportCodes();
            break;

        case '/api/stats':
            (new \App\Controllers\AdminController())->apiStats();
            break;

        default:
            http_response_code(404);
            echo "<h1>404 - Halaman Tidak Ditemukan</h1>";
            echo "<p>Halaman yang Anda cari tidak tersedia.</p>";
            break;
    }
} catch (\PDOException $exception) {
    http_response_code(500);
    ?>
    <!doctype html>
    <html lang="id">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Database Error</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
        <link rel="stylesheet" href="assets/css/style.css">
    </head>

    <body class="flex min-h-screen items-center justify-center px-4 py-10">
        <main class="glass w-full max-w-md rounded-[2rem] p-8 text-center text-white" data-aos="fade-up">
            <div
                class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-red-600 text-3xl text-white shadow-xl">
                <i class="fa-solid fa-database"></i>
            </div>
            <h1 class="text-2xl font-black mb-3">Koneksi Database Gagal</h1>
            <p class="text-sm text-slate-300 leading-6 mb-6">Aplikasi belum terhubung ke database. Jika ini instalasi baru,
                Anda harus menjalankan halaman setup terlebih dahulu.</p>

            <div
                class="mb-6 rounded-xl bg-black/40 p-4 text-left font-mono text-[10px] text-red-300 overflow-x-auto max-h-36">
                <strong>Detail Error:</strong><br>
                <?= e($exception->getMessage()); ?>
            </div>

            <a href="/install"
                class="btn-ripple flex items-center justify-center gap-2 rounded-2xl bg-[#f6c85f] px-5 py-3.5 font-black text-[#07172f] shadow-xl transition hover:bg-white">
                <i class="fa-solid fa-circle-play"></i> Mulai Instalasi
            </a>
        </main>
    </body>

    </html>
    <?php
    exit;
}
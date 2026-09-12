<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header("Location: {$path}");
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function is_student_logged_in(): bool
{
    return isset($_SESSION['user_id'], $_SESSION['user_username']);
}

function require_student(): void
{
    if (!is_student_logged_in()) {
        redirect('login.php');
    }
}

function is_admin_logged_in(): bool
{
    return isset($_SESSION['admin_id'], $_SESSION['admin_username']);
}

function require_admin(): void
{
    if (!is_admin_logged_in()) {
        redirect('login.php');
    }
}

function page_title(string $title): string
{
    return e($title . ' - ' . APP_NAME);
}
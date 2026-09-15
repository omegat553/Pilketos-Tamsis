<?php
declare(strict_types=1);

namespace App\Core;

class Security
{
    public static function sendHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        // HTTP Security Headers
        header("X-Frame-Options: DENY");
        header("X-Content-Type-Options: nosniff");
        header("Referrer-Policy: same-origin");
        
        // Dynamic Content-Security-Policy supporting CDN dependencies used in UI
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://unpkg.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://unpkg.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data:; connect-src 'self';");
    }

    public static function escape(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    public static function csrfToken(): string
    {
        if (!Session::has('csrf_token')) {
            Session::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return (string) Session::get('csrf_token');
    }

    public static function verifyCsrf(?string $token): bool
    {
        $sessionToken = Session::get('csrf_token');
        return is_string($token)
            && is_string($sessionToken)
            && hash_equals($sessionToken, $token);
    }

    public static function sanitizeInput(mixed $data): string
    {
        if ($data === null) {
            return '';
        }
        return trim(strip_tags((string) $data));
    }
}

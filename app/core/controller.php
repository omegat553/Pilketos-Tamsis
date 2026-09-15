<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function render(string $view, array $data = []): void
    {
        $viewFile = __DIR__ . '/../Views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(404);
            exit("View {$view} tidak ditemukan.");
        }

        // Extract variables to be used in view templates
        extract($data);

        require_once $viewFile;
    }

    protected function redirect(string $url): never
    {
        if (defined('BASE_URL') && BASE_URL !== '' && str_starts_with($url, '/') && !str_starts_with($url, BASE_URL . '/')) {
            $url = BASE_URL . $url;
        }
        header("Location: {$url}");
        exit;
    }

    protected function json(mixed $data, int $statusCode = 200): never
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_THROW_ON_ERROR);
        exit;
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function validateCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? null;
        if (!Security::verifyCsrf($token)) {
            Session::flash('error', 'Sesi tidak valid. Silakan coba lagi.');
            $this->redirect($_SERVER['REQUEST_URI'] ?? '/');
        }
    }
}

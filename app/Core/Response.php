<?php

namespace App\Core;

class Response
{
    public function redirect(string $path = '/', int $status = 302): never
    {
        $url = $this->url($path);
        header('Location: ' . $url, true, $status);
        exit;
    }

    public function back(): never
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        header('Location: ' . $referer, true, 302);
        exit;
    }

    public function url(string $path = ''): string
    {
        $base = App::baseUrl();
        if ($path === '' || $path === '/') {
            return $base . $path;
        }
        return $base . '/' . ltrim($path, '/');
    }

    public function asset(string $path): string
    {
        $path = ltrim($path, '/');
        if (str_starts_with($path, 'uploads/')) {
            return $this->url($path);
        }
        $url = $this->url('/static/' . $path);
        $file = \App\Core\App::instance()->path('public') . '/static/' . $path;
        if (is_file($file)) {
            $url .= '?v=' . filemtime($file);
        }
        return $url;
    }

    public function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function abort(int $status = 404, string $message = 'Not Found'): never
    {
        http_response_code($status);
        if (Auth::check() && !$this->isApi()) {
            View::render('errors/error', ['code' => $status, 'message' => $message]);
        }
        echo htmlspecialchars($message);
        exit;
    }

    public function download(string $content, string $filename, string $mime = 'application/octet-stream'): never
    {
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($content));
        echo $content;
        exit;
    }

    public function isApi(): bool
    {
        return str_starts_with($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    }
}
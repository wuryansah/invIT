<?php

namespace App\Core;

use RuntimeException;

class Request
{
    public string $method;
    public string $uri;

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->uri = $this->parseUri();
    }

    private function parseUri(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $uri = rawurldecode($uri);

        $base = App::basePath();

        // Windows filesystems are case-insensitive, so SCRIPT_NAME may carry the
        // on-disk casing (…/invIT/…) while the browser URL uses any casing
        // (…/invit/…). Strip the base path case-insensitively.
        if ($base !== '' && strncasecmp($uri, $base, strlen($base)) === 0) {
            $uri = substr($uri, strlen($base));
        }
        if (str_ends_with($uri, '/index.php')) {
            $uri = substr($uri, 0, -9);
        }

        return $uri === '' ? '/' : $uri;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    public function file(string $key): ?array
    {
        return isset($_FILES[$key]) && ($_FILES[$key]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE
            ? $_FILES[$key]
            : null;
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function ajax(): bool
    {
        return strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHTTPREQUEST';
    }

    public function has(string $key): bool
    {
        return isset($_POST[$key]) || isset($_GET[$key]);
    }

    /**
     * Persist the current request data into the session (flash) so it can be
     * re-populated on a redirect after a failed validation.
     */
    public function flash(): void
    {
        $_SESSION['_old_input'] = $this->all();
    }

    public function old(string $key, mixed $default = null): mixed
    {
        return $_SESSION['_old_input'][$key] ?? $default;
    }
}
<?php

/**
 * Global helper functions.
 */

use App\Core\App;
use App\Core\Auth;
use App\Core\Response;

if (!function_exists('config')) {
    function config(?string $key = null, mixed $default = null): mixed
    {
        $cfg = App::instance()->config;
        if ($key === null) {
            return $cfg;
        }
        $value = $cfg;
        foreach (explode('.', $key) as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }
        return $value;
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        return app_response()->url($path);
    }
}

if (!function_exists('asset_url')) {
    function asset_url(string $path): string
    {
        return app_response()->asset($path);
    }
}

if (!function_exists('qr_url')) {
    function qr_url(string $code): string
    {
        return rtrim(setting('qr_public_url', 'http://112.78.151.22:8000'), '/') . '/invit/public/qr/' . urlencode($code);
    }
}

if (!function_exists('app_response')) {
    function app_response(): Response
    {
        return new Response();
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path = '/', int $status = 302): never
    {
        app_response()->redirect($path, $status);
    }
}

if (!function_exists('back')) {
    function back(): never
    {
        app_response()->back();
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = null): mixed
    {
        return $_SESSION['_old_input'][$key] ?? $default;
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('flash')) {
    function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][$type] = $message;
    }
}

if (!function_exists('flash_take')) {
    function flash_take(): array
    {
        $messages = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $messages;
    }
}

if (!function_exists('has_flash_error')) {
    function has_flash_error(): bool
    {
        return !empty($_SESSION['_flash']['error']);
    }
}

if (!function_exists('redirect_with')) {
    function redirect_with(string $type, string $message, string $path = '/'): never
    {
        flash($type, $message);
        redirect($path);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return \App\Core\Csrf::token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('now')) {
    function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('today')) {
    function today(): string
    {
        return date('Y-m-d');
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $date, string $format = 'd M Y'): string
    {
        if (!$date || $date === '0000-00-00') {
            return '—';
        }
        return date($format, strtotime($date));
    }
}

if (!function_exists('format_datetime')) {
    function format_datetime(?string $datetime): string
    {
        if (!$datetime) {
            return '—';
        }
        return date('d M Y H:i', strtotime($datetime));
    }
}

if (!function_exists('format_money')) {
    function format_money(mixed $amount): string
    {
        return 'Rp ' . number_format((float)$amount, 0, ',', '.');
    }
}

if (!function_exists('status_badge')) {
    function status_badge(string $status): string
    {
        $map = [
            'Available'        => 'success',
            'Assigned'         => 'info',
            'On Loan'          => 'warning',
            'Under Maintenance'=> 'primary',
            'Damaged'          => 'danger',
            'Lost'             => 'dark',
            'Retired'          => 'secondary',
            'Disposed'         => 'secondary',
            'Active'           => 'success',
            'Returned'         => 'secondary',
            'Overdue'          => 'danger',
            'Due Today'        => 'warning',
            'New'              => 'success',
            'Good'             => 'info',
            'Fair'             => 'warning',
            'Need Maintenance' => 'primary',
            'Critical'         => 'danger',
        ];
        $class = $map[$status] ?? 'secondary';
        return '<span class="badge bg-' . $class . '">' . e($status) . '</span>';
    }
}

if (!function_exists('truncate')) {
    function truncate(string $text, int $length = 60): string
    {
        return mb_strlen($text) > $length ? mb_substr($text, 0, $length - 1) . '…' : $text;
    }
}

if (!function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return \App\Models\Setting::value($key, $default);
    }
}

if (!function_exists('auth')) {
    function auth(): ?\App\Models\User
    {
        return Auth::user();
    }
}

if (!function_exists('is_route')) {
    function is_route(string $segment): bool
    {
        $uri = trim($_SERVER['REQUEST_URI'] ?? '/', '/');
        return str_starts_with($uri, trim($segment, '/'));
    }
}

if (!function_exists('ago')) {
    function ago(?string $datetime): string
    {
        if (!$datetime) return '—';
        $diff = time() - strtotime($datetime);
        if ($diff < 60) return 'just now';
        if ($diff < 3600) return floor($diff / 60) . ' min ago';
        if ($diff < 86400) return floor($diff / 3600) . ' hr ago';
        if ($diff < 604800) return floor($diff / 86400) . ' days ago';
        return format_date($datetime);
    }
}
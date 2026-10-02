<?php

namespace App\Core;

class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function generate(): string
    {
        return '<input type="hidden" name="_token" value="' . self::token() . '">';
    }

    public static function verify(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sent = $_POST['_token'] ?? '';
            if (!hash_equals(self::token(), (string)$sent)) {
                app_response()->abort(419, 'Page expired. Please try again.');
            }
        }
    }

    public static function tokenField(): string
    {
        return self::generate();
    }
}
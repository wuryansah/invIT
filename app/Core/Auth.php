<?php

namespace App\Core;

use App\Models\Employee;
use App\Models\User;

class Auth
{
    private static ?User $user = null;

    public static function attempt(string $email, string $password): bool
    {
        $user = User::whereFirst(['email' => $email]);
        if (!$user) {
            return false;
        }
        if (!$user['is_active']) {
            return false;
        }
        if (!password_verify($password, $user['password'])) {
            return false;
        }
        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            User::update((int)$user['id'], ['password' => password_hash($password, PASSWORD_DEFAULT)]);
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        User::update((int)$user['id'], ['last_login_at' => now()]);
        self::$user = null;
        return true;
    }

    public static function user(): ?User
    {
        if (self::$user === null) {
            $id = $_SESSION['user_id'] ?? null;
            if (!$id) {
                return null;
            }
            self::$user = User::find((int)$id);
            if (!self::$user || !self::$user['is_active']) {
                self::$user = null;
                return null;
            }
        }
        return self::$user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        return self::user()['id'] ?? null;
    }

    public static function role(): ?string
    {
        return self::user()['role'] ?? null;
    }

    public static function name(): string
    {
        return self::user()['name'] ?? 'Guest';
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    public static function isStaff(): bool
    {
        return in_array(self::role(), ['admin', 'staff'], true);
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    /** Return the linked employee record for the logged in user, if any. */
    public static function employee(): ?Employee
    {
        $user = self::user();
        if (!$user || empty($user['employee_id'])) {
            return null;
        }
        return \App\Models\Employee::find((int)$user['employee_id']);
    }
}
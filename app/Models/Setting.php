<?php

namespace App\Models;

class Setting extends Model
{
    protected static string $table = 'settings';
    protected static array $fillable = ['key', 'value'];

    public static function value(string $key, mixed $default = null): mixed
    {
        $row = \App\Core\Database::first("SELECT value FROM settings WHERE `key` = ?", [$key]);
        return $row['value'] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $exists = \App\Core\Database::first("SELECT id FROM settings WHERE `key` = ?", [$key]);
        if ($exists) {
            \App\Core\Database::run("UPDATE settings SET `value` = ? WHERE `key` = ?", [(string)$value, $key]);
        } else {
            \App\Core\Database::run("INSERT INTO settings (`key`, `value`, created_at) VALUES (?, ?, ?)", [
                $key, (string)$value, now()
            ]);
        }
    }
}
<?php

namespace App\Models;

use App\Core\Database;

class Notification extends Model
{
    protected static string $table = 'notifications';
    protected static array $fillable = ['type', 'title', 'message', 'user_id', 'is_read'];

    public static function push(string $type, string $title, string $message, ?int $userId = null): void
    {
        self::create([
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'user_id' => $userId,
        ]);
    }

    /** Notifications visible to the current user. */
    public static function forUser(?int $userId = null, int $limit = 20): array
    {
        $userId = $userId ?? (\App\Core\Auth::id() ?? 0);
        return Database::select(
            "SELECT * FROM notifications
             WHERE user_id = ? OR user_id IS NULL
             ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }

    public static function unreadCount(?int $userId = null): int
    {
        $userId = $userId ?? (\App\Core\Auth::id() ?? 0);
        return (int)(Database::first(
            "SELECT COUNT(*) c FROM notifications
             WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0",
            [$userId]
        )['c'] ?? 0);
    }
}
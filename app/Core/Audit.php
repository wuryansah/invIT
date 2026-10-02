<?php

namespace App\Core;

use App\Models\AuditLog;

class Audit
{
    /**
     * Record an important action in the audit log.
     */
    public static function log(string $action, string $description, ?int $userId = null): void
    {
        $userId = $userId ?? Auth::id();
        AuditLog::create([
            'user_id'     => $userId,
            'action'      => $action,
            'description' => $description,
            'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? 'cli',
        ]);
    }
}
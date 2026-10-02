<?php

namespace App\Models;

class AuditLog extends Model
{
    protected static string $table = 'audit_logs';
    protected static array $fillable = ['user_id', 'action', 'description', 'ip_address'];

    public static function allWithUser(int $perPage = 25, int $page = 1, array $filters = []): array
    {
        $sql = "SELECT a.*, u.name user_name, u.role FROM audit_logs a LEFT JOIN users u ON u.id = a.user_id WHERE 1";
        $bind = [];

        if (!empty($filters['user'])) {
            $sql .= " AND a.user_id = ?";
            $bind[] = (int)$filters['user'];
        }
        if (!empty($filters['action'])) {
            $sql .= " AND a.action LIKE ?";
            $bind[] = '%' . $filters['action'] . '%';
        }
        if (!empty($filters['from']) && !empty($filters['to'])) {
            $sql .= " AND DATE(a.created_at) BETWEEN ? AND ?";
            $bind[] = $filters['from'];
            $bind[] = $filters['to'];
        }

        $sql .= " ORDER BY a.created_at DESC, a.id DESC";
        return static::paginate($sql, $bind, $perPage, $page);
    }
}
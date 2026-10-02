<?php

namespace App\Models;

class Maintenance extends Model
{
    protected static string $table = 'asset_maintenance';
    protected static array $fillable = [
        'maintenance_no', 'asset_id', 'started_at', 'completed_at', 'type',
        'description', 'cost', 'performed_by', 'status', 'notes', 'created_by',
    ];

    public static function nextNumber(): string
    {
        $row = \App\Core\Database::first("SELECT MAX(id) max_id FROM asset_maintenance");
        return 'MNT-' . str_pad((string)((int)$row['max_id'] + 1), 5, '0', STR_PAD_LEFT);
    }
}
<?php

namespace App\Models;

use App\Core\Database;

class Assignment extends Model
{
    protected static string $table = 'asset_assignments';
    protected static array $fillable = [
        'assignment_no', 'asset_id', 'employee_id', 'department_id', 'assignment_date',
        'assigned_by', 'purpose', 'location', 'condition_on_assign', 'accessories',
        'notes', 'is_current', 'status',
    ];

    /** Next sequential assignment number. */
    public static function nextNumber(): string
    {
        $row = \App\Core\Database::first("SELECT MAX(id) max_id FROM asset_assignments");
        return 'ASG-' . str_pad((string)((int)$row['max_id'] + 1), 5, '0', STR_PAD_LEFT);
    }

    public static function historyForAsset(int $assetId): array
    {
        return Database::where(['asset_id' => $assetId], 'assignment_date', 'DESC');
    }
}
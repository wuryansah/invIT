<?php

namespace App\Models;

class Transfer extends Model
{
    protected static string $table = 'asset_transfers';
    protected static array $fillable = [
        'transfer_no', 'asset_id', 'previous_employee_id', 'new_employee_id',
        'previous_department_id', 'new_department_id', 'transfer_date', 'reason',
        'previous_condition', 'current_condition', 'authorized_by', 'notes',
    ];

    public static function nextNumber(): string
    {
        $row = \App\Core\Database::first("SELECT MAX(id) max_id FROM asset_transfers");
        return 'TRF-' . str_pad((string)((int)$row['max_id'] + 1), 5, '0', STR_PAD_LEFT);
    }
}
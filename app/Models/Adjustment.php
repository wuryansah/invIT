<?php

namespace App\Models;

class Adjustment extends Model
{
    protected static string $table = 'inventory_adjustments';
    protected static array $fillable = [
        'adjustment_no', 'asset_id', 'type', 'qty_before', 'qty_change', 'qty_after',
        'old_status', 'new_status', 'old_location', 'new_location', 'reason', 'performed_by',
    ];

    public static function nextNumber(): string
    {
        $row = \App\Core\Database::first("SELECT MAX(id) max_id FROM inventory_adjustments");
        return 'ADJ-' . str_pad((string)((int)$row['max_id'] + 1), 5, '0', STR_PAD_LEFT);
    }
}
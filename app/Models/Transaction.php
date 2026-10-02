<?php

namespace App\Models;

use App\Core\Database;

class Transaction extends Model
{
    protected static string $table = 'asset_transactions';
    protected static array $fillable = ['asset_id', 'type', 'title', 'description', 'user_id'];

    public const TYPES = [
        'Purchase', 'Received', 'Assigned', 'Released', 'Loaned', 'Returned',
        'Transferred', 'Maintenance', 'Adjustment', 'Status Change', 'Created',
        'Updated', 'Deleted', 'Retired', 'Disposed', 'Reported Lost',
    ];

    public static function historyForAsset(int $assetId): array
    {
        return Database::select(
            "SELECT t.*, u.name user_name FROM asset_transactions t
             LEFT JOIN users u ON u.id = t.user_id
             WHERE t.asset_id = ? ORDER BY t.created_at ASC, t.id ASC",
            [$assetId]
        );
    }

    public static function monthlyCount(string $typeLike, int $months = 6): array
    {
        $rows = Database::select(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') ym, COUNT(*) c
             FROM asset_transactions
             WHERE (type = ? OR type = ?) AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
             GROUP BY ym ORDER BY ym ASC",
            [$typeLike, $typeLike, $months]
        );
        $labels = [];
        $counts = [];
        $start = strtotime(date('Y-m-01'));
        for ($i = $months - 1; $i >= 0; $i--) {
            $key = date('Y-m', strtotime("-{$i} month", $start));
            $labels[] = date('M Y', strtotime($key . '-01'));
            $count = 0;
            foreach ($rows as $r) {
                if ($r['ym'] === $key) {
                    $count = (int)$r['c'];
                }
            }
            $counts[] = $count;
        }
        return [$labels, $counts];
    }
}
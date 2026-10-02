<?php

namespace App\Models;

use App\Core\Database;

class Asset extends Model
{
    protected static string $table = 'assets';
    protected static array $fillable = [
        'asset_code', 'asset_name', 'category_id', 'brand', 'model', 'serial_number',
        'ip_address', 'product_number', 'specification', 'purchase_date', 'purchase_price',
        'warranty_expiration', 'supplier', 'invoice_number', 'building', 'floor',
        'room', 'storage_location', 'department_id', 'status', 'condition',
        'current_employee_id', 'photo', 'notes', 'created_by', 'updated_by',
    ];

    public const STATUS_AVAILABLE = 'Available';
    public const STATUS_ASSIGNED = 'Assigned';
    public const STATUS_ON_LOAN = 'On Loan';
    public const STATUS_MAINTENANCE = 'Under Maintenance';
    public const STATUS_DAMAGED = 'Damaged';
    public const STATUS_LOST = 'Lost';
    public const STATUS_RETIRED = 'Retired';
    public const STATUS_DISPOSED = 'Disposed';

    public const STATUSES = [
        self::STATUS_AVAILABLE, self::STATUS_ASSIGNED, self::STATUS_ON_LOAN,
        self::STATUS_MAINTENANCE, self::STATUS_DAMAGED, self::STATUS_LOST,
        self::STATUS_RETIRED, self::STATUS_DISPOSED,
    ];

    public const CONDITIONS = ['New', 'Good', 'Fair', 'Damaged', 'Critical', 'Need Maintenance'];

    /** Build the next asset code for a category, e.g. IT-LAP-0001. */
    public static function nextCode(int $categoryId): string
    {
        $category = Category::find($categoryId);
        $prefix = trim(strtoupper($category['code'] ?? 'IT'));
        $row = Database::first(
            "SELECT MAX(CAST(SUBSTRING_INDEX(asset_code, '-', -1) AS UNSIGNED)) max_seq
             FROM assets WHERE category_id = ?",
            [$categoryId]
        );
        $seq = ((int)$row['max_seq']) + 1;
        return $prefix . '-' . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }

    public function category(): ?Category
    {
        return $this->category_id ? Category::find((int)$this->category_id) : null;
    }

    public function holder(): ?Employee
    {
        return $this->current_employee_id
            ? Employee::find((int)$this->current_employee_id)
            : null;
    }

    public function holderName(): string
    {
        return $this->holder()['name'] ?? '—';
    }

    public function department(): ?Department
    {
        return $this->department_id ? Department::find((int)$this->department_id) : null;
    }

    /** Record a transaction history entry for this asset. */
    public function recordTransaction(string $type, string $title, string $description = '', ?int $userId = null): void
    {
        Transaction::create([
            'asset_id'    => (int)$this->id,
            'type'        => $type,
            'title'       => $title,
            'description' => $description,
            'user_id'     => $userId ?? (\App\Core\Auth::id() ?? 0),
        ]);
    }

    public static function canBeIssued(array|Asset $asset): bool
    {
        return $asset['status'] === self::STATUS_AVAILABLE;
    }

    public static function searchQuery(array $params): array
    {
        $sql = "SELECT a.*, c.name category_name, d.name department_name, e.name employee_name
                FROM assets a
                LEFT JOIN asset_categories c ON c.id = a.category_id
                LEFT JOIN departments d ON d.id = a.department_id
                LEFT JOIN employees e ON e.id = a.current_employee_id
                WHERE 1";
        $bind = [];

        if (!empty($params['q'])) {
            $sql .= " AND (a.asset_code LIKE ? OR a.asset_name LIKE ? OR a.serial_number LIKE ?
                          OR a.brand LIKE ? OR a.model LIKE ? OR a.product_number LIKE ?)";
            $term = '%' . $params['q'] . '%';
            array_push($bind, $term, $term, $term, $term, $term, $term);
        }
        if (!empty($params['category'])) {
            $sql .= " AND a.category_id = ?";
            $bind[] = (int)$params['category'];
        }
        if (!empty($params['status'])) {
            $sql .= " AND a.status = ?";
            $bind[] = $params['status'];
        }
        if (!empty($params['condition'])) {
            $sql .= " AND a.condition = ?";
            $bind[] = $params['condition'];
        }
        if (!empty($params['department'])) {
            $sql .= " AND a.department_id = ?";
            $bind[] = (int)$params['department'];
        }
        if (!empty($params['building'])) {
            $sql .= " AND a.building LIKE ?";
            $bind[] = '%' . $params['building'] . '%';
        }
        if (!empty($params['employee'])) {
            $sql .= " AND (a.current_employee_id = ? OR a.current_employee_id IN
                        (SELECT id FROM employees WHERE name LIKE ?))";
            $bind[] = (int)$params['employee'];
            $bind[] = '%' . $params['employee'] . '%';
        }

        $sql .= " ORDER BY a.asset_code ASC";
        return [$sql, $bind];
    }
}
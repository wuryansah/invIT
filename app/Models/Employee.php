<?php

namespace App\Models;

class Employee extends Model
{
    protected static string $table = 'employees';
    protected static array $fillable = [
        'employee_number', 'name', 'department_id', 'position', 'email', 'phone',
        'office_location', 'status', 'photo', 'notes',
    ];

    public static function department(int $id): ?Department
    {
        return Department::find((int)$id);
    }

    /** Currently assigned (permanent) assets for an employee. */
    public static function assignedAssets(int $id): array
    {
        return Asset::where(['current_employee_id' => $id, 'status' => 'Assigned']);
    }

    /** Active loans (not yet returned) for an employee. */
    public static function activeLoans(int $id): array
    {
        return Loan::where(['employee_id' => $id, 'status' => 'Active'], 'loan_date', 'DESC');
    }

    public static function fullName(array $employee): string
    {
        return $employee['name'] ?? '—';
    }
}
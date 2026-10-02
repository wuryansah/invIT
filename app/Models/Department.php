<?php

namespace App\Models;

class Department extends Model
{
    protected static string $table = 'departments';
    protected static array $fillable = ['name', 'code', 'description'];

    public static function employeeCount(int $id): int
    {
        return Employee::count(['department_id' => $id]);
    }

    /** Asset count currently owned / located under the department. */
    public static function assetCount(int $id): int
    {
        return Asset::count(['department_id' => $id]);
    }
}
<?php

namespace App\Models;

class Loan extends Model
{
    protected static string $table = 'asset_loans';
    protected static array $fillable = [
        'loan_no', 'asset_id', 'employee_id', 'department_id', 'loan_date',
        'expected_return_date', 'actual_return_date', 'purpose', 'condition_before',
        'condition_after_return', 'accessories_included', 'approved_by', 'issued_by',
        'returned_to', 'employee_acknowledged', 'notes', 'status',
    ];

    /** Next sequential loan number. */
    public static function nextNumber(): string
    {
        $row = \App\Core\Database::first("SELECT MAX(id) max_id FROM asset_loans");
        return 'LOAN-' . str_pad((string)((int)$row['max_id'] + 1), 5, '0', STR_PAD_LEFT);
    }

    /** Status of a loan, computed from dates: Active | Due Today | Overdue | Returned. */
    public static function derivedStatus(array|Loan $loan): string
    {
        if ($loan['status'] === 'Returned') {
            return 'Returned';
        }
        $due = $loan['expected_return_date'] ?? null;
        if (!$due || $due === '0000-00-00') {
            return 'Active';
        }
        $today = today();
        if ($due === $today) {
            return 'Due Today';
        }
        if ($due < $today) {
            return 'Overdue';
        }
        return 'Active';
    }

    public function asset(): ?Asset
    {
        return Asset::find((int)$this->asset_id);
    }

    public function employee(): ?Employee
    {
        return Employee::find((int)$this->employee_id);
    }
}
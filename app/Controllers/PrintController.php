<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\View;
use App\Models\Assignment;
use App\Models\Loan;

class PrintController extends BaseController
{
    public function handoverAssignment(Request $request, int $id): void
    {
        $row = Database::first(
            "SELECT aa.*, a.asset_code, a.asset_name, a.serial_number, a.brand, a.model, a.condition asset_condition,
                    c.name category_name, e.name employee_name, e.employee_number, e.position,
                    d.name department_name, u.name assigned_by_name
             FROM asset_assignments aa
             LEFT JOIN assets a ON a.id = aa.asset_id
             LEFT JOIN asset_categories c ON c.id = a.category_id
             LEFT JOIN employees e ON e.id = aa.employee_id
             LEFT JOIN departments d ON d.id = aa.department_id
             LEFT JOIN users u ON u.id = aa.assigned_by
             WHERE aa.id = ?",
            [$id]
        );
        if (!$row) {
            app_response()->abort(404, 'Assignment not found.');
        }

        View::render('print/handover-assignment', [
            'pageTitle' => 'IT Asset Handover Form',
            'row'       => $row,
        ], null);
    }

    public function handoverLoan(Request $request, int $id): void
    {
        $row = Database::first(
            "SELECT l.*, a.asset_code, a.asset_name, a.serial_number, a.brand, a.model, a.condition asset_condition,
                    c.name category_name, e.name employee_name, e.employee_number, e.position,
                    d.name department_name, i.name issued_by_name, k.name approved_by_name
             FROM asset_loans l
             LEFT JOIN assets a ON a.id = l.asset_id
             LEFT JOIN asset_categories c ON c.id = a.category_id
             LEFT JOIN employees e ON e.id = l.employee_id
             LEFT JOIN departments d ON d.id = l.department_id
             LEFT JOIN users i ON i.id = l.issued_by
             LEFT JOIN users k ON k.id = l.approved_by
             WHERE l.id = ?",
            [$id]
        );
        if (!$row) {
            app_response()->abort(404, 'Loan not found.');
        }

        View::render('print/handover-loan', [
            'pageTitle' => 'IT Equipment Loan Form',
            'row'       => $row,
        ], null);
    }

    public function returnForm(Request $request, int $id): void
    {
        $row = Database::first(
            "SELECT l.*, a.asset_code, a.asset_name, a.serial_number, a.brand, a.model, a.condition asset_condition,
                    c.name category_name, e.name employee_name, e.employee_number, e.position,
                    d.name department_name, i.name issued_by_name, v.name received_by_name
             FROM asset_loans l
             LEFT JOIN assets a ON a.id = l.asset_id
             LEFT JOIN asset_categories c ON c.id = a.category_id
             LEFT JOIN employees e ON e.id = l.employee_id
             LEFT JOIN departments d ON d.id = l.department_id
             LEFT JOIN users i ON i.id = l.issued_by
             LEFT JOIN users v ON v.id = l.received_by
             WHERE l.id = ?",
            [$id]
        );
        if (!$row) {
            app_response()->abort(404, 'Loan not found.');
        }

        View::render('print/return-form', [
            'pageTitle' => 'Asset Return Form',
            'row'       => $row,
        ], null);
    }
}
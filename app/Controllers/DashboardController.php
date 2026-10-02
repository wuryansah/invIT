<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;
use App\Models\Asset;
use App\Models\Employee;
use App\Models\Loan;
use App\Models\Notification;
use App\Models\Transaction;

class DashboardController extends BaseController
{
    public function index(): void
    {
        if (Auth::role() === 'employee') {
            redirect('/my-assets');
        }

        $today = today();

        $statusCounts = array_fill_keys(Asset::STATUSES, 0);
        foreach (Database::select("SELECT status, COUNT(*) c FROM assets GROUP BY status") as $row) {
            $statusCounts[$row['status']] = (int)$row['c'];
        }

        $employeeTotal = Employee::count();
        $withAssigned = (int)Database::first(
            "SELECT COUNT(DISTINCT current_employee_id) c FROM assets WHERE current_employee_id IS NOT NULL AND status = 'Assigned'"
        )['c'];
        $withActiveLoans = (int)Database::first(
            "SELECT COUNT(DISTINCT employee_id) c FROM asset_loans WHERE status = 'Active'"
        )['c'];

        $activeLoans = Loan::count(['status' => 'Active']);
        $dueToday = (int)Database::first(
            "SELECT COUNT(*) c FROM asset_loans WHERE status = 'Active' AND expected_return_date = ?", [$today]
        )['c'];
        $overdue = (int)Database::first(
            "SELECT COUNT(*) c FROM asset_loans WHERE status = 'Active' AND expected_return_date < ?", [$today]
        )['c'];
        $returnedThisMonth = (int)Database::first(
            "SELECT COUNT(*) c FROM asset_loans WHERE status = 'Returned'
             AND actual_return_date IS NOT NULL AND MONTH(actual_return_date) = MONTH(CURDATE())
             AND YEAR(actual_return_date) = YEAR(CURDATE())"
        )['c'];

        $byCategory = Database::select(
            "SELECT c.name, COUNT(*) c FROM assets a LEFT JOIN asset_categories c ON c.id = a.category_id GROUP BY c.name ORDER BY c DESC"
        );
        $byStatus = Database::select("SELECT status, COUNT(*) c FROM assets GROUP BY status ORDER BY c DESC");
        $byDepartment = Database::select(
            "SELECT COALESCE(d.name, 'Unassigned') name, COUNT(*) c
             FROM assets a LEFT JOIN departments d ON d.id = a.department_id GROUP BY d.name ORDER BY c DESC"
        );

        [$loanLabels, $loanCounts] = Transaction::monthlyCount('Loaned');
        [$assignLabels, $assignCounts] = Transaction::monthlyCount('Assigned');

        $unreadNotifications = Notification::unreadCount();

        View::render('dashboard/index', [
            'pageTitle'   => 'Dashboard',
            'pageModule'  => 'dashboard',
            'statusCounts'=> $statusCounts,
            'employeeTotal' => $employeeTotal,
            'withAssigned'  => $withAssigned,
            'withActiveLoans' => $withActiveLoans,
            'activeLoans'   => $activeLoans,
            'dueToday'      => $dueToday,
            'overdue'       => $overdue,
            'returnedThisMonth' => $returnedThisMonth,
            'byCategory'    => $byCategory,
            'byStatus'      => $byStatus,
            'byDepartment'  => $byDepartment,
            'loanLabels'    => $loanLabels,
            'loanCounts'    => $loanCounts,
            'assignLabels'  => $assignLabels,
            'assignCounts'  => $assignCounts,
            'unreadNotifications' => $unreadNotifications,
            'recentLoans'   => Database::select(
                "SELECT l.*, e.name employee_name, a.asset_code, a.asset_name
                 FROM asset_loans l
                 LEFT JOIN employees e ON e.id = l.employee_id
                 LEFT JOIN assets a ON a.id = l.asset_id
                 WHERE l.status = 'Active' ORDER BY l.loan_date DESC LIMIT 8"
            ),
        ]);
    }
}
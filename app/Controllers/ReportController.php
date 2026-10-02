<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Excel;
use App\Core\Request;
use App\Core\View;
use App\Models\Adjustment;
use App\Models\Asset;
use App\Models\Loan;

class ReportController extends BaseController
{
    public function index(): void
    {
        View::render('reports/index', [
            'pageTitle'  => 'Reports',
            'pageModule' => 'reports',
        ]);
    }

    public function inventory(Request $request): void
    {
        [$sql, $bind] = Asset::searchQuery([
            'category'  => $request->get('category'),
            'status'    => $request->get('status'),
            'condition' => $request->get('condition'),
            'department'=> $request->get('department'),
        ]);
        $assets = Database::select($sql, $bind);

        View::render('reports/inventory', [
            'pageTitle'  => 'Inventory Report',
            'pageModule' => 'reports',
            'assets'     => $assets,
            'filters'    => $request->all(),
        ]);
    }

    public function employeeAssets(): void
    {
        $rows = Database::select(
            "SELECT e.id employee_id, e.employee_number, e.name employee_name, d.name department_name,
                    COALESCE(GROUP_CONCAT(DISTINCT CONCAT(a.asset_code, ' (', a.asset_name, ')') ORDER BY a.asset_code SEPARATOR ', '), '') assigned_assets,
                    COALESCE((SELECT COUNT(*) FROM asset_loans l WHERE l.employee_id = e.id AND l.status = 'Active'), 0) active_loans
             FROM employees e
             LEFT JOIN departments d ON d.id = e.department_id
             LEFT JOIN assets a ON a.current_employee_id = e.id AND a.status = 'Assigned'
             WHERE e.status = 'Active'
             GROUP BY e.id
             ORDER BY e.name"
        );

        View::render('reports/employee-assets', [
            'pageTitle' => 'Employee Asset Report',
            'pageModule'=> 'reports',
            'rows'      => $rows,
        ]);
    }

    public function loans(Request $request): void
    {
        $sql = "SELECT l.*, a.asset_code, a.asset_name, e.name employee_name, d.name department_name
                FROM asset_loans l
                LEFT JOIN assets a ON a.id = l.asset_id
                LEFT JOIN employees e ON e.id = l.employee_id
                LEFT JOIN departments d ON d.id = l.department_id
                WHERE 1";
        $bind = [];

        $status = $request->get('status');
        if ($status) {
            if ($status === 'Active' || $status === 'Due Today' || $status === 'Overdue') {
                $rows = Database::select($sql . ($status === 'Overdue'
                    ? " AND l.status = 'Active' AND l.expected_return_date < CURDATE()"
                    : ($status === 'Due Today'
                        ? " AND l.status = 'Active' AND l.expected_return_date = CURDATE()"
                        : " AND l.status = 'Active'")), $bind);
                foreach ($rows as &$r) {
                    $r['report_status'] = Loan::derivedStatus($r);
                }
            } else {
                $sql .= " AND l.status = 'Returned'";
                $rows = Database::select($sql, $bind);
                foreach ($rows as &$r) {
                    $r['report_status'] = 'Returned';
                }
            }
        } else {
            $rows = Database::select($sql . " ORDER BY l.loan_date DESC", $bind);
            foreach ($rows as &$r) {
                $r['report_status'] = Loan::derivedStatus($r);
            }
        }

        View::render('reports/loans', [
            'pageTitle' => 'Loan Report',
            'pageModule'=> 'reports',
            'rows'      => $rows,
            'statusFilter' => $status,
        ]);
    }

    public function department(): void
    {
        $rows = Database::select(
            "SELECT COALESCE(d.name, 'Unassigned') department_name,
                    COUNT(a.id) total_assets,
                    SUM(a.status = 'Available') available,
                    SUM(a.status = 'Assigned') assigned,
                    SUM(a.status = 'On Loan') on_loan,
                    SUM(a.status = 'Under Maintenance') maintenance,
                    SUM(a.status IN ('Damaged','Lost','Retired','Disposed')) other
             FROM assets a
             LEFT JOIN departments d ON d.id = a.department_id
             GROUP BY d.name
             ORDER BY total_assets DESC"
        );

        View::render('reports/department', [
            'pageTitle' => 'Department Report',
            'pageModule'=> 'reports',
            'rows'      => $rows,
        ]);
    }

    public function maintenance(): void
    {
        $rows = Database::select(
            "SELECT m.*, a.asset_code, a.asset_name, c.name category_name, u.name created_by_name
             FROM asset_maintenance m
             LEFT JOIN assets a ON a.id = m.asset_id
             LEFT JOIN asset_categories c ON c.id = a.category_id
             LEFT JOIN users u ON u.id = m.created_by
             ORDER BY m.status = 'In Progress' DESC, m.started_at DESC"
        );

        View::render('reports/maintenance', [
            'pageTitle' => 'Maintenance Report',
            'pageModule'=> 'reports',
            'rows'      => $rows,
        ]);
    }

    public function assetHistory(Request $request, int $id): void
    {
        $asset = Asset::findOrFail($id);
        $history = Database::select(
            "SELECT t.*, u.name user_name FROM asset_transactions t
             LEFT JOIN users u ON u.id = t.user_id
             WHERE t.asset_id = ? ORDER BY t.created_at DESC, t.id DESC",
            [$id]
        );

        View::render('reports/asset-history', [
            'pageTitle' => 'Asset History - ' . $asset['asset_code'],
            'pageModule'=> 'reports',
            'asset'     => $asset,
            'rows'      => $history,
        ]);
    }

    public function export(Request $request, string $type): void
    {
        switch ($type) {
            case 'inventory':
            case 'asset-history':
            case 'employee-assets':
            case 'maintenance':
            case 'adjustments':
            case 'loans-active':
            case 'loans-returned':
            case 'loans-overdue':
            case 'loans-history':
            case 'department':
                $this->doExport($type);
                // doExport never returns
                break;
            default:
                app_response()->abort(404, 'Unknown export type.');
        }
    }

    private function doExport(string $type): never
    {
        switch ($type) {
            case 'inventory':
                $rows = Database::select(
                    "SELECT a.*, c.name category_name, d.name department_name, e.name employee_name
                     FROM assets a
                     LEFT JOIN asset_categories c ON c.id = a.category_id
                     LEFT JOIN departments d ON d.id = a.department_id
                     LEFT JOIN employees e ON e.id = a.current_employee_id
                     ORDER BY a.asset_code"
                );
                $headers = ['Asset Code', 'Asset Name', 'Category', 'Brand', 'Model', 'Serial Number', 'Status', 'Condition', 'Employee', 'Department', 'Building', 'Floor', 'Room', 'Purchase Date', 'Price', 'Warranty', 'Supplier'];
                $data = array_map(fn($r) => [
                    $r['asset_code'], $r['asset_name'], $r['category_name'], $r['brand'], $r['model'],
                    $r['serial_number'], $r['status'], $r['condition'], $r['employee_name'] ?? '', $r['department_name'] ?? '',
                    $r['building'], $r['floor'], $r['room'], $r['purchase_date'], (float)$r['purchase_price'], $r['warranty_expiration'], $r['supplier'],
                ], $rows);
                Excel::download('Inventory.xlsx', $headers, $data, 'Inventory');

            case 'employee-assets':
                $rows = Database::select(
                    "SELECT e.employee_number, e.name employee_name, COALESCE(d.name,'') department_name,
                            a.asset_code, a.asset_name, a.serial_number, a.status
                     FROM employees e
                     LEFT JOIN departments d ON d.id = e.department_id
                     LEFT JOIN assets a ON a.current_employee_id = e.id
                     WHERE e.status = 'Active' AND (a.id IS NOT NULL)
                     ORDER BY e.name, a.asset_code"
                );
                $headers = ['Employee Number', 'Employee', 'Department', 'Asset Code', 'Asset Name', 'Serial Number', 'Status'];
                $data = array_map(fn($r) => array_values($r), $rows);
                Excel::download('Employee_Assets.xlsx', $headers, $data, 'Employee Assets');

            case 'maintenance':
                $rows = Database::select(
                    "SELECT m.maintenance_no, a.asset_code, a.asset_name, m.type, m.started_at, m.completed_at, m.status, m.cost, m.performed_by, m.description
                     FROM asset_maintenance m LEFT JOIN assets a ON a.id = m.asset_id ORDER BY m.started_at DESC"
                );
                $headers = ['Maintenance No', 'Asset Code', 'Asset Name', 'Type', 'Start', 'Completed', 'Status', 'Cost', 'Performed By', 'Description'];
                $data = array_map(fn($r) => array_values($r), $rows);
                Excel::download('Maintenance_Report.xlsx', $headers, $data, 'Maintenance');

            case 'adjustments':
                $rows = Database::select(
                    "SELECT g.adjustment_no, a.asset_code, a.asset_name, g.type, g.old_status, g.new_status, g.reason, u.name performed_by, g.created_at
                     FROM inventory_adjustments g
                     LEFT JOIN assets a ON a.id = g.asset_id
                     LEFT JOIN users u ON u.id = g.performed_by ORDER BY g.created_at DESC"
                );
                $headers = ['Adjustment No', 'Asset Code', 'Asset Name', 'Type', 'Old Status', 'New Status', 'Reason', 'By', 'Date'];
                $data = array_map(fn($r) => array_values($r), $rows);
                Excel::download('Inventory_Adjustments.xlsx', $headers, $data, 'Adjustments');

            case 'loans-active':
            case 'loans-overdue':
            case 'loans-returned':
            case 'loans-history':
                $extra = " AND l.status = 'Active'";
                $filename = 'Active_Loans.xlsx';
                $title = 'Active Loans';
                if ($type === 'loans-overdue') {
                    $extra = " AND l.status = 'Active' AND l.expected_return_date < CURDATE()";
                    $filename = 'Overdue_Loans.xlsx';
                    $title = 'Overdue Loans';
                } elseif ($type === 'loans-returned') {
                    $extra = " AND l.status = 'Returned'";
                    $filename = 'Returned_Loans.xlsx';
                    $title = 'Returned Loans';
                } elseif ($type === 'loans-history') {
                    $extra = '';
                    $filename = 'Loan_History.xlsx';
                    $title = 'Loan History';
                }
                $rows = Database::select(
                    "SELECT l.loan_no, e.name employee_name, COALESCE(d.name,'') department_name, a.asset_code, a.asset_name,
                            l.loan_date, l.expected_return_date, l.actual_return_date, l.purpose, l.status
                     FROM asset_loans l
                     LEFT JOIN employees e ON e.id = l.employee_id
                     LEFT JOIN departments d ON d.id = l.department_id
                     LEFT JOIN assets a ON a.id = l.asset_id
                     WHERE 1 $extra ORDER BY l.loan_date DESC"
                );
                $headers = ['Loan No', 'Employee', 'Department', 'Asset Code', 'Asset Name', 'Loan Date', 'Due Date', 'Return Date', 'Purpose', 'Status'];
                $data = array_map(fn($r) => array_values($r), $rows);
                Excel::download($filename, $headers, $data, $title);

            case 'department':
                $rows = Database::select(
                    "SELECT COALESCE(d.name,'Unassigned') dept, COUNT(*) total,
                            SUM(a.status='Available') available, SUM(a.status='Assigned') assigned,
                            SUM(a.status='On Loan') on_loan, SUM(a.status='Under Maintenance') maintenance
                     FROM assets a LEFT JOIN departments d ON d.id = a.department_id GROUP BY d.name"
                );
                $headers = ['Department', 'Total', 'Available', 'Assigned', 'On Loan', 'Maintenance'];
                $data = array_map(fn($r) => array_values($r), $rows);
                Excel::download('Department_Report.xlsx', $headers, $data, 'Department');

            case 'asset-history':
            default:
                $id = (int)($_GET['asset_id'] ?? 0);
                $asset = Asset::findOrFail($id);
                $rows = Database::select(
                    "SELECT t.type, t.title, t.description, u.name user_name, t.created_at
                     FROM asset_transactions t LEFT JOIN users u ON u.id = t.user_id
                     WHERE t.asset_id = ? ORDER BY t.created_at DESC", [$id]
                );
                $headers = ['Type', 'Title', 'Description', 'By', 'Date'];
                $data = array_map(fn($r) => array_values($r), $rows);
                Excel::download('Asset_History.xlsx', $headers, $data, 'History');
        }
    }
}
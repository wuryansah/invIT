<?php

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Database;
use App\Core\Request;
use App\Core\Validator;
use App\Core\View;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Loan;
use App\Models\Maintenance;
use App\Models\Notification;
use App\Models\Transaction;

class LoanController extends BaseController
{
    public function index(Request $request): void
    {
        $sql = "SELECT l.*, a.asset_code, a.asset_name, a.status asset_status, a.condition asset_condition,
                       c.name category_name, e.name employee_name, d.name department_name
                FROM asset_loans l
                LEFT JOIN assets a ON a.id = l.asset_id
                LEFT JOIN asset_categories c ON c.id = a.category_id
                LEFT JOIN employees e ON e.id = l.employee_id
                LEFT JOIN departments d ON d.id = l.department_id
                WHERE 1";
        $bind = [];

        $filters = [
            'department' => $request->get('department'),
            'employee'   => $request->get('employee'),
            'category'   => $request->get('category'),
            'status'     => $request->get('status'),
            'from'       => $request->get('from'),
            'to'         => $request->get('to'),
            'q'          => $request->get('q'),
        ];

        if ($filters['department']) {
            $sql .= " AND l.department_id = ?";
            $bind[] = (int)$filters['department'];
        }
        if ($filters['category']) {
            $sql .= " AND a.category_id = ?";
            $bind[] = (int)$filters['category'];
        }
        if ($filters['employee']) {
            $sql .= " AND (l.employee_id = ? OR e.name LIKE ?)";
            $bind[] = (int)$filters['employee'];
            $bind[] = '%' . $filters['employee'] . '%';
        }
        if ($filters['from']) {
            $sql .= " AND l.loan_date >= ?";
            $bind[] = $filters['from'];
        }
        if ($filters['to']) {
            $sql .= " AND l.loan_date <= ?";
            $bind[] = $filters['to'];
        }
        if ($filters['q']) {
            $sql .= " AND (l.loan_no LIKE ? OR a.asset_code LIKE ? OR a.asset_name LIKE ? OR e.name LIKE ?)";
            $term = '%' . $filters['q'] . '%';
            array_push($bind, $term, $term, $term, $term);
        }

        $sql .= " ORDER BY l.loan_date DESC, l.id DESC";
        $rows = Database::select($sql, $bind);

        // Compute derived status and optionally filter by it in memory.
        $rows = array_map(fn($r) => $r + ['derived_status' => Loan::derivedStatus($r)], $rows);

        if ($filters['status']) {
            $rows = array_values(array_filter($rows, fn($r) => $r['derived_status'] === $filters['status']));
        }

        $page = max(1, (int)$request->get('page', 1));
        $perPage = 20;
        $total = count($rows);
        $items = array_slice($rows, ($page - 1) * $perPage, $perPage);
        $lastPage = (int)ceil($total / $perPage);
        $base = preg_replace('/[?&]page=\d+/', '', $_SERVER['REQUEST_URI'] ?? '/');
        $query = parse_url($base, PHP_URL_QUERY);
        $base = strtok($base, '?');
        $separator = $query ? '&' : '?';
        $pagination = compact('items', 'total', 'page', 'perPage', 'lastPage', 'base', 'query', 'separator');

        View::render('loans/index', [
            'pageTitle'  => 'Loan Monitoring',
            'pageModule' => 'loans',
            'pagination' => $pagination,
            'filters'    => $filters,
            'departments'=> Department::all('name'),
            'categories' => Category::all('name'),
            'employees'  => Employee::all('name'),
            'list'       => $items,
        ]);
    }

    public function create(): void
    {
        $available = Database::select(
            "SELECT a.*, c.name category_name FROM assets a
             LEFT JOIN asset_categories c ON c.id = a.category_id
             WHERE a.status = 'Available' ORDER BY a.asset_code"
        );

        View::render('loans/form', [
            'pageTitle'  => 'New Loan',
            'pageModule' => 'loans',
            'assets'     => $available,
            'employees'  => Employee::where(['status' => 'Active'], 'name'),
        ]);
    }

    public function store(Request $request): void
    {
        $errors = (new Validator())->validate($request->all(), [
            'asset_id'        => 'required',
            'employee_id'     => 'required',
            'loan_date'       => 'required|date',
            'expected_return_date' => 'required|date|after_or_equal:loan_date',
        ]);
        if ($errors) {
            redirect_with('error', $this->firstError($errors), '/loans/create');
        }

        $asset = Asset::findOrFail((int)$request->post('asset_id'));
        $employee = Employee::findOrFail((int)$request->post('employee_id'));

        if (!Asset::canBeIssued($asset)) {
            redirect_with('error', "Asset {$asset['asset_code']} has status {$asset['status']} and cannot be loaned.", '/loans/create');
        }

        Database::transaction(function () use ($asset, $employee, $request) {
            $loanNo = Loan::nextNumber();
            $loanId = Loan::create([
                'loan_no'             => $loanNo,
                'asset_id'            => $asset['id'],
                'employee_id'         => $employee['id'],
                'department_id'       => $employee['department_id'],
                'loan_date'           => $request->post('loan_date'),
                'expected_return_date'=> $request->post('expected_return_date'),
                'purpose'             => trim($request->post('purpose')),
                'condition_before'    => $request->post('condition_before') ?: $asset['condition'],
                'accessories_included'=> trim($request->post('accessories_included')),
                'approved_by'         => $request->post('approved_by') ?: \App\Core\Auth::id(),
                'issued_by'           => \App\Core\Auth::id(),
                'employee_acknowledged'=> $request->post('employee_acknowledged') ? 1 : 0,
                'notes'               => trim($request->post('notes')),
                'status'              => 'Active',
            ]);

            Asset::update($asset['id'], ['status' => Asset::STATUS_ON_LOAN]);

            $updated = Asset::findOrFail($asset['id']);
            $updated->recordTransaction(
                'Loaned',
                "Loaned to {$employee['name']}",
                ($request->post('purpose') ?: '') . ' | Expected return: ' . $request->post('expected_return_date') . ' | ' . $loanNo,
                \App\Core\Auth::id()
            );

            Notification::push('loan', 'Equipment loaned', "{$asset['asset_code']} ({$asset['asset_name']}) loaned to {$employee['name']} until " . $request->post('expected_return_date') . '.');
            Audit::log('Asset Loan', "Loaned {$asset['asset_code']} to {$employee['name']} ({$loanNo}).");
        });

        redirect_with('success', "Loan created. {$asset['asset_code']} is now On Loan.", '/loans');
    }

    public function show(Request $request, int $id): void
    {
        $loan = Database::first(
            "SELECT l.*, a.asset_code, a.asset_name, a.serial_number, a.category_id,
                    c.name category_name, e.name employee_name, e.employee_number, e.position,
                    d.name department_name, i.name issued_by_name, k.name approved_by_name, r.name returned_to_name, v.name received_by_name
             FROM asset_loans l
             LEFT JOIN assets a ON a.id = l.asset_id
             LEFT JOIN asset_categories c ON c.id = a.category_id
             LEFT JOIN employees e ON e.id = l.employee_id
             LEFT JOIN departments d ON d.id = l.department_id
             LEFT JOIN users i ON i.id = l.issued_by
             LEFT JOIN users k ON k.id = l.approved_by
             LEFT JOIN users r ON r.id = l.returned_to
             LEFT JOIN users v ON v.id = l.received_by
             WHERE l.id = ?",
            [$id]
        );
        if (!$loan) {
            app_response()->abort(404, 'Loan not found.');
        }
        $loan['derived_status'] = Loan::derivedStatus($loan);

        View::render('loans/show', [
            'pageTitle'  => 'Loan ' . $loan['loan_no'],
            'pageModule' => 'loans',
            'loan'       => $loan,
        ]);
    }

    public function processReturn(Request $request, int $id): void
    {
        $loan = Loan::findOrFail($id);
        if ($loan['status'] === 'Returned') {
            redirect_with('error', 'This loan has already been returned.', "/loans/{$id}");
        }

        $errors = (new Validator())->validate($request->all(), [
            'return_date' => 'required|date',
            'condition_after' => 'in:New,Good,Fair,Damaged,Critical',
        ]);
        if ($errors) {
            redirect_with('error', $this->firstError($errors), "/loans/{$id}");
        }

        $asset = Asset::findOrFail((int)$loan['asset_id']);
        $employee = Employee::findOrFail((int)$loan['employee_id']);
        $conditionAfter = $request->post('condition_after') ?: $asset['condition'];
        $damaged = in_array($conditionAfter, ['Damaged', 'Critical'], true);

        Database::transaction(function () use ($loan, $asset, $employee, $conditionAfter, $damaged, $request, $id) {
            Loan::update($id, [
                'actual_return_date'     => $request->post('return_date'),
                'condition_after_return' => $conditionAfter,
                'accessories_returned'   => trim($request->post('accessories_returned')),
                'missing_accessories'    => trim($request->post('missing_accessories')),
                'damage_description'     => trim($request->post('damage_description')),
                'notes'                  => trim($request->post('notes')),
                'returned_to'            => \App\Core\Auth::id(),
                'received_by'            => \App\Core\Auth::id(),
                'status'                 => 'Returned',
            ]);

            $newStatus = $damaged ? Asset::STATUS_MAINTENANCE : Asset::STATUS_AVAILABLE;
            Asset::update($asset['id'], [
                'status'              => $newStatus,
                'current_employee_id' => null,
                'condition'           => $conditionAfter,
            ]);

            if ($damaged) {
                $mntNo = Maintenance::nextNumber();
                Maintenance::create([
                    'maintenance_no' => $mntNo,
                    'asset_id'       => $asset['id'],
                    'started_at'     => $request->post('return_date'),
                    'type'           => 'Repair',
                    'description'    => trim($request->post('damage_description')) ?: 'Damaged on return',
                    'status'         => 'In Progress',
                    'notes'          => 'Opened automatically from loan return.',
                    'created_by'     => \App\Core\Auth::id(),
                ]);
            }

            $updated = Asset::findOrFail($asset['id']);
            $desc = "Returned from {$employee['name']} (loan " . $loan['loan_no'] . "). Condition after return: {$conditionAfter}.";
            $updated->recordTransaction('Returned', "Returned from {$employee['name']}", $desc, \App\Core\Auth::id());

            if ($damaged) {
                Notification::push('damage', 'Asset damaged on return', "{$asset['asset_code']} came back {$conditionAfter}. Moved to maintenance.");
            } else {
                Notification::push('return', 'Asset returned', "{$asset['asset_code']} returned by {$employee['name']}");
            }
            Audit::log('Asset Return', "{$asset['asset_code']} returned by {$employee['name']} ({$loan['loan_no']}), status now {$newStatus}.");
        });

        redirect_with('success', "Return processed. {$asset['asset_code']} is now " . ($damaged ? 'Under Maintenance' : 'Available') . '.', '/loans');
    }

    private function firstError(array $errors): string
    {
        return reset($errors);
    }
}
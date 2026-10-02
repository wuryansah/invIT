<?php

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Database;
use App\Core\Excel;
use App\Core\Request;
use App\Core\Validator;
use App\Core\View;
use App\Models\Department;
use App\Models\Employee;

class EmployeeController extends BaseController
{
    private const PER_PAGE = 15;

    public function index(Request $request): void
    {
        $filters = [
            'q'          => $request->get('q'),
            'department' => $request->get('department'),
            'status'     => $request->get('status'),
        ];

        $sql = "SELECT e.*, d.name department_name FROM employees e LEFT JOIN departments d ON d.id = e.department_id WHERE 1";
        $bind = [];

        if (!empty($filters['q'])) {
            $sql .= " AND (e.name LIKE ? OR e.employee_number LIKE ? OR e.email LIKE ? OR e.position LIKE ?)";
            $term = '%' . $filters['q'] . '%';
            array_push($bind, $term, $term, $term, $term);
        }
        if (!empty($filters['department'])) {
            $sql .= " AND e.department_id = ?";
            $bind[] = (int)$filters['department'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND e.status = ?";
            $bind[] = $filters['status'];
        }

        $sql .= " ORDER BY e.name ASC";
        $paginated = Employee::paginate($sql, $bind, self::PER_PAGE, (int)$request->get('page', 1));

        View::render('employees/index', [
            'pageTitle'  => 'Employees',
            'pageModule' => 'employees',
            'pagination' => $paginated,
            'filters'    => $filters,
            'departments'=> Department::all('name'),
        ]);
    }

    public function create(): void
    {
        View::render('employees/form', [
            'pageTitle'  => 'New Employee',
            'pageModule' => 'employees',
            'employee'   => null,
            'departments'=> Department::all('name'),
        ]);
    }

    public function store(Request $request): void
    {
        $errors = (new Validator())->validate($request->all(), [
            'employee_number' => 'required|unique:employees,employee_number',
            'name'            => 'required|max:120',
            'email'           => 'email',
        ]);

        if ($errors) {
            $request->flash();
            redirect_with('error', $this->firstError($errors), '/employees/create');
        }

        $data = [
            'employee_number' => trim($request->post('employee_number')),
            'name'            => trim($request->post('name')),
            'department_id'   => $request->post('department_id') ?: null,
            'position'        => trim($request->post('position')),
            'email'           => trim($request->post('email')) ?: null,
            'phone'           => trim($request->post('phone')),
            'office_location' => trim($request->post('office_location')),
            'status'          => $request->post('status') === 'Inactive' ? 'Inactive' : 'Active',
            'notes'           => trim($request->post('notes')),
        ];

        $photo = $this->uploadPhoto($request->file('photo'));
        if (has_flash_error()) {
            redirect('/employees/create');
        }
        $data['photo'] = $photo;

        $id = Employee::create($data);
        Audit::log('Employee Creation', "Created employee {$data['name']} ({$data['employee_number']}).");

        redirect_with('success', "Employee {$data['name']} added.", "/employees/{$id}");
    }

    public function show(Request $request, int $id): void
    {
        $employee = Employee::findOrFail($id);

        $assigned = Database::select(
            "SELECT a.*, c.name category_name FROM assets a
             LEFT JOIN asset_categories c ON c.id = a.category_id
             WHERE a.current_employee_id = ? AND a.status = 'Assigned' ORDER BY a.asset_code ASC",
            [$id]
        );
        $loans = Database::select(
            "SELECT l.*, a.asset_code, a.asset_name, a.status asset_status
             FROM asset_loans l LEFT JOIN assets a ON a.id = l.asset_id
             WHERE l.employee_id = ? ORDER BY l.loan_date DESC",
            [$id]
        );
        $history = Database::select(
            "SELECT a.asset_code, t.*, u.name user_name
             FROM asset_transactions t
             LEFT JOIN assets a ON a.id = t.asset_id
             LEFT JOIN users u ON u.id = t.user_id
             WHERE t.asset_id IN (SELECT asset_id FROM asset_assignments WHERE employee_id = ?
                                  UNION SELECT asset_id FROM asset_loans WHERE employee_id = ?
                                  UNION SELECT asset_id FROM asset_transfers WHERE previous_employee_id = ? OR new_employee_id = ?)
             ORDER BY t.created_at DESC LIMIT 100",
            [$id, $id, $id, $id]
        );

        View::render('employees/show', [
            'pageTitle'  => $employee['name'],
            'pageModule' => 'employees',
            'employee'   => $employee,
            'department' => $employee['department_id'] ? Department::find((int)$employee['department_id']) : null,
            'assigned'   => $assigned,
            'loans'      => $loans,
            'history'    => $history,
        ]);
    }

    public function edit(Request $request, int $id): void
    {
        $employee = Employee::findOrFail($id);
        View::render('employees/form', [
            'pageTitle'  => 'Edit Employee - ' . $employee['name'],
            'pageModule' => 'employees',
            'employee'   => $employee,
            'departments'=> Department::all('name'),
        ]);
    }

    public function update(Request $request, int $id): void
    {
        $employee = Employee::findOrFail($id);
        $errors = (new Validator())->validate($request->all(), [
            'employee_number' => "required|unique:employees,employee_number,$id",
            'name'            => 'required|max:120',
            'email'           => 'email',
        ]);
        if ($errors) {
            redirect_with('error', $this->firstError($errors), "/employees/$id/edit");
        }

        $data = [
            'employee_number' => trim($request->post('employee_number')),
            'name'            => trim($request->post('name')),
            'department_id'   => $request->post('department_id') ?: null,
            'position'        => trim($request->post('position')),
            'email'           => trim($request->post('email')) ?: null,
            'phone'           => trim($request->post('phone')),
            'office_location' => trim($request->post('office_location')),
            'status'          => $request->post('status') === 'Inactive' ? 'Inactive' : 'Active',
            'notes'           => trim($request->post('notes')),
        ];

        $photo = $this->uploadPhoto($request->file('photo'), $employee['photo']);
        if (has_flash_error()) {
            redirect("/employees/$id/edit");
        }
        if ($photo) {
            $data['photo'] = $photo;
        }

        Employee::update($id, $data);
        Audit::log('Employee Update', "Updated employee {$data['name']}.");

        redirect_with('success', 'Employee updated.', "/employees/{$id}");
    }

    public function destroy(Request $request, int $id): void
    {
        $employee = Employee::findOrFail($id);

        $inUse = Database::first(
            "SELECT COUNT(*) c FROM assets WHERE current_employee_id = ? AND status = 'Assigned'", [$id]
        )['c'];
        $activeLoan = Database::first(
            "SELECT COUNT(*) c FROM asset_loans WHERE employee_id = ? AND status = 'Active'", [$id]
        )['c'];

        if ((int)$inUse > 0 || (int)$activeLoan > 0) {
            redirect_with('error', 'Cannot delete: employee still holds assigned assets or active loans.', '/employees');
        }

        Employee::delete($id);
        Audit::log('Employee Deletion', "Deleted employee {$employee['name']}.");
        redirect_with('success', 'Employee deleted.', '/employees');
    }

    public function export(): void
    {
        $employees = Database::select(
            "SELECT e.*, d.name department_name FROM employees e LEFT JOIN departments d ON d.id = e.department_id ORDER BY e.name"
        );
        $headers = ['Employee Number', 'Name', 'Department', 'Position', 'Email', 'Phone', 'Office Location', 'Status'];
        $rows = [];
        foreach ($employees as $emp) {
            $rows[] = [$emp['employee_number'], $emp['name'], $emp['department_name'] ?? '', $emp['position'], $emp['email'], $emp['phone'], $emp['office_location'], $emp['status']];
        }
        Excel::download('Employees.xlsx', $headers, $rows, 'Employees');
    }

    private function firstError(array $errors): string
    {
        return reset($errors);
    }
}
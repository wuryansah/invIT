<?php

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Database;
use App\Core\Request;
use App\Core\Validator;
use App\Core\View;
use App\Models\Asset;
use App\Models\Assignment;
use App\Models\Employee;
use App\Models\Notification;

class AssignmentController extends BaseController
{
    public function index(Request $request): void
    {
        $sql = "SELECT aa.*, a.asset_code, a.asset_name, e.name employee_name, d.name department_name, u.name assigned_by_name
                FROM asset_assignments aa
                LEFT JOIN assets a ON a.id = aa.asset_id
                LEFT JOIN employees e ON e.id = aa.employee_id
                LEFT JOIN departments d ON d.id = aa.department_id
                LEFT JOIN users u ON u.id = aa.assigned_by
                WHERE 1";
        $bind = [];

        if ($f = $request->get('status')) {
            $sql .= " AND aa.status = ?";
            $bind[] = $f;
        }
        if ($f = $request->get('q')) {
            $sql .= " AND (a.asset_code LIKE ? OR a.asset_name LIKE ? OR e.name LIKE ? OR aa.assignment_no LIKE ?)";
            $term = '%' . $f . '%';
            array_push($bind, $term, $term, $term, $term);
        }

        $sql .= " ORDER BY aa.assignment_date DESC, aa.id DESC";
        $paginated = Assignment::paginate($sql, $bind, 20, (int)$request->get('page', 1));

        View::render('assignments/index', [
            'pageTitle'  => 'Assignments',
            'pageModule' => 'assets',
            'pagination' => $paginated,
            'statusFilter' => $request->get('status'),
            'q'          => $request->get('q'),
        ]);
    }

    public function create(): void
    {
        $availableAssets = Database::select(
            "SELECT a.*, c.name category_name FROM assets a
             LEFT JOIN asset_categories c ON c.id = a.category_id
             WHERE a.status = 'Available' ORDER BY a.asset_code"
        );
        $employees = Employee::where(['status' => 'Active'], 'name');

        View::render('assignments/form', [
            'pageTitle'  => 'Assign Asset',
            'pageModule' => 'assets',
            'assets'     => $availableAssets,
            'employees'  => $employees,
        ]);
    }

    public function store(Request $request): void
    {
        $errors = (new Validator())->validate($request->all(), [
            'asset_id'        => 'required',
            'employee_id'     => 'required',
            'assignment_date' => 'required|date',
        ]);
        if ($errors) {
            redirect_with('error', $this->firstError($errors), '/assign/create');
        }

        $asset = Asset::findOrFail((int)$request->post('asset_id'));
        $employee = Employee::findOrFail((int)$request->post('employee_id'));

        if (!Asset::canBeIssued($asset)) {
            redirect_with('error', "Asset {$asset['asset_code']} has status {$asset['status']} and cannot be assigned.", '/assign/create');
        }

        $departmentId = $employee['department_id'];

        Database::transaction(function () use ($asset, $employee, $departmentId, $request) {
            $assignmentNo = Assignment::nextNumber();
            Assignment::create([
                'assignment_no'      => $assignmentNo,
                'asset_id'           => $asset['id'],
                'employee_id'        => $employee['id'],
                'department_id'      => $departmentId,
                'assignment_date'    => $request->post('assignment_date'),
                'assigned_by'        => \App\Core\Auth::id(),
                'purpose'            => trim($request->post('purpose')),
                'location'           => trim($request->post('location')) ?: $employee['office_location'],
                'condition_on_assign'=> $request->post('condition_on_assign') ?: $asset['condition'],
                'accessories'        => trim($request->post('accessories')),
                'notes'              => trim($request->post('notes')),
                'status'             => 'Active',
                'is_current'         => 1,
            ]);

            Asset::update($asset['id'], [
                'status'              => Asset::STATUS_ASSIGNED,
                'current_employee_id' => $employee['id'],
                'department_id'       => $departmentId,
                'condition'           => $request->post('condition_on_assign') ?: $asset['condition'],
            ]);

            $updated = Asset::findOrFail($asset['id']);
            $updated->recordTransaction(
                'Assigned',
                "Assigned to {$employee['name']}",
                ($departmentId ? 'Department: ' . $employee['department_id'] : '') . ' | ' . trim($request->post('purpose')),
                \App\Core\Auth::id()
            );

            Notification::push('assignment', 'Asset assigned', "{$asset['asset_code']} ({$asset['asset_name']}) assigned to {$employee['name']}.");
            Audit::log('Asset Assignment', "Assigned {$asset['asset_code']} to {$employee['name']} ({$assignmentNo}).");
        });

        redirect_with('success', "Asset {$asset['asset_code']} assigned to {$employee['name']}.", '/assignments');
    }

    public function unassign(Request $request, int $id): void
    {
        $assignment = Assignment::findOrFail($id);
        $asset = Asset::findOrFail((int)$assignment['asset_id']);
        $employee = Employee::findOrFail((int)$assignment['employee_id']);

        if ($assignment['status'] !== 'Active') {
            redirect_with('error', 'This assignment is already closed.', '/assignments');
        }
        if ($asset['status'] !== Asset::STATUS_ASSIGNED) {
            redirect_with('error', 'Asset is not currently in Assigned status.', '/assignments');
        }

        Database::transaction(function () use ($assignment, $asset, $employee, $id) {
            Assignment::update($id, [
                'status'        => 'Returned',
                'is_current'    => 0,
                'unassigned_at' => now(),
            ]);

            $newStatus = $request->post('condition_after') ?: $asset['condition'];
            if (in_array($newStatus, ['Damaged', 'Critical'], true)) {
                $result = 'Under Maintenance';
            } else {
                $result = 'Available';
            }

            Asset::update($asset['id'], [
                'status'              => $result,
                'current_employee_id' => null,
                'condition'           => $newStatus,
            ]);

            $updated = Asset::findOrFail($asset['id']);
            $updated->recordTransaction('Released', "Released from {$employee['name']}", "Returned to IT inventory. Condition: {$newStatus}.", \App\Core\Auth::id());

            Notification::push('return', 'Asset released', "{$asset['asset_code']} released from {$employee['name']}.");
            Audit::log('Asset Release', "Released {$asset['asset_code']} from {$employee['name']}.");
        });

        redirect_with('success', "Asset {$asset['asset_code']} released from {$employee['name']}.", '/assignments');
    }

    private function firstError(array $errors): string
    {
        return reset($errors);
    }
}
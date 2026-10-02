<?php

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Database;
use App\Core\Request;
use App\Core\Validator;
use App\Core\View;
use App\Models\Asset;
use App\Models\Employee;
use App\Models\Notification;
use App\Models\Transfer;

class TransferController extends BaseController
{
    private const PER_PAGE = 20;

    public function index(Request $request): void
    {
        $sql = "SELECT t.*, a.asset_code, a.asset_name, pe.name previous_employee_name, ne.name new_employee_name,
                       pd.name previous_department_name, nd.name new_department_name, u.name authorized_by_name
                FROM asset_transfers t
                LEFT JOIN assets a ON a.id = t.asset_id
                LEFT JOIN employees pe ON pe.id = t.previous_employee_id
                LEFT JOIN employees ne ON ne.id = t.new_employee_id
                LEFT JOIN departments pd ON pd.id = t.previous_department_id
                LEFT JOIN departments nd ON nd.id = t.new_department_id
                LEFT JOIN users u ON u.id = t.authorized_by
                WHERE 1";
        $bind = [];

        if ($f = $request->get('q')) {
            $sql .= " AND (t.transfer_no LIKE ? OR a.asset_code LIKE ? OR pe.name LIKE ? OR ne.name LIKE ?)";
            $term = '%' . $f . '%';
            array_push($bind, $term, $term, $term, $term);
        }

        $sql .= " ORDER BY t.transfer_date DESC, t.id DESC";
        $paginated = Transfer::paginate($sql, $bind, self::PER_PAGE, (int)$request->get('page', 1));

        View::render('transfers/index', [
            'pageTitle'  => 'Asset Transfers',
            'pageModule' => 'assets',
            'pagination' => $paginated,
            'q'          => $request->get('q'),
        ]);
    }

    public function create(): void
    {
        $assigned = Database::select(
            "SELECT a.*, c.name category_name, e.name employee_name FROM assets a
             LEFT JOIN asset_categories c ON c.id = a.category_id
             LEFT JOIN employees e ON e.id = a.current_employee_id
             WHERE a.status = 'Assigned' ORDER BY a.asset_code"
        );

        View::render('transfers/form', [
            'pageTitle'  => 'Transfer Asset',
            'pageModule' => 'assets',
            'assets'     => $assigned,
            'employees'  => Employee::where(['status' => 'Active'], 'name'),
        ]);
    }

    public function store(Request $request): void
    {
        $errors = (new Validator())->validate($request->all(), [
            'asset_id'    => 'required',
            'employee_id' => 'required',
            'transfer_date' => 'required|date',
            'reason'      => 'required|max:255',
        ]);

        if ($errors) {
            redirect_with('error', $this->firstError($errors), '/transfers/create');
        }

        $asset = Asset::findOrFail((int)$request->post('asset_id'));
        $previous = Employee::findOrFail((int)$asset['current_employee_id']);
        $newEmployee = Employee::findOrFail((int)$request->post('employee_id'));

        if ($asset['status'] !== Asset::STATUS_ASSIGNED || empty($asset['current_employee_id'])) {
            redirect_with('error', "Only assets that are currently assigned can be transferred.", '/transfers/create');
        }
        if ($asset['current_employee_id'] === $newEmployee['id']) {
            redirect_with('error', 'Asset is already with that employee.', '/transfers/create');
        }

        Database::transaction(function () use ($asset, $previous, $newEmployee, $request) {
            $transferNo = Transfer::nextNumber();
            Transfer::create([
                'transfer_no'            => $transferNo,
                'asset_id'               => $asset['id'],
                'previous_employee_id'   => $previous['id'],
                'new_employee_id'        => $newEmployee['id'],
                'previous_department_id' => $previous['department_id'],
                'new_department_id'      => $newEmployee['department_id'],
                'transfer_date'          => $request->post('transfer_date'),
                'reason'                 => trim($request->post('reason')),
                'previous_condition'     => $asset['condition'],
                'current_condition'      => $request->post('current_condition') ?: $asset['condition'],
                'authorized_by'          => \App\Core\Auth::id(),
                'notes'                  => trim($request->post('notes')),
            ]);

            Asset::update($asset['id'], [
                'current_employee_id' => $newEmployee['id'],
                'department_id'       => $newEmployee['department_id'],
                'status'              => Asset::STATUS_ASSIGNED,
                'condition'           => $request->post('current_condition') ?: $asset['condition'],
            ]);

            $updated = Asset::findOrFail($asset['id']);
            $updated->recordTransaction(
                'Transferred',
                "Transferred {$previous['name']} → {$newEmployee['name']}",
                'Reason: ' . trim($request->post('reason')) . ' | ' . $transferNo,
                \App\Core\Auth::id()
            );

            Notification::push('transfer', 'Asset transferred', "{$asset['asset_code']} transferred from {$previous['name']} to {$newEmployee['name']}.");
            Audit::log('Asset Transfer', "{$asset['asset_code']} transferred {$previous['name']} → {$newEmployee['name']} ({$transferNo}).");
        });

        redirect_with('success', "Asset transferred to {$newEmployee['name']}.", '/transfers');
    }

    private function firstError(array $errors): string
    {
        return reset($errors);
    }
}
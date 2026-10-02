<?php

namespace App\Controllers;

use App\Core\Audit;
use App\Core\App;
use App\Core\Database;
use App\Core\Excel;
use App\Core\Request;
use App\Core\Validator;
use App\Core\View;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Loan;
use App\Models\Notification;
use App\Models\Transaction;

class AssetController extends BaseController
{
    public function index(Request $request): void
    {
        $filters = [
            'q'         => $request->get('q'),
            'category'  => $request->get('category'),
            'status'    => $request->get('status'),
            'condition' => $request->get('condition'),
            'department'=> $request->get('department'),
            'building'  => $request->get('building'),
        ];

        [$sql, $bind] = Asset::searchQuery($filters);
        $page = (int)($request->get('page', 1));
        $perPage = 20;
        $paginated = Asset::paginate($sql, $bind, $perPage, $page);

        View::render('assets/index', [
            'pageTitle'  => 'Assets',
            'pageModule' => 'assets',
            'pagination' => $paginated,
            'filters'    => $filters,
            'categories' => Category::all('name'),
            'statuses'   => Asset::STATUSES,
            'conditions' => Asset::CONDITIONS,
            'departments'=> Department::all('name'),
        ]);
    }

    public function create(): void
    {
        View::render('assets/form', [
            'pageTitle'  => 'New Asset',
            'pageModule' => 'assets',
            'asset'      => null,
            'categories' => Category::all('name'),
            'departments'=> Department::all('name'),
            'statuses'   => Asset::STATUSES,
            'conditions' => Asset::CONDITIONS,
            'employees'  => Employee::where(['status' => 'Active'], 'name'),
            'nextCode'   => '',
        ]);
    }

    public function store(Request $request): void
    {
        $validator = new Validator();
        $errors = $validator->validate($request->all(), [
            'asset_code'   => 'required|unique:assets,asset_code',
            'asset_name'   => 'required|max:120',
            'category_id'  => 'required',
            'serial_number'=> 'unique:assets,serial_number',
            'purchase_date'=> 'date',
            'warranty_expiration' => 'date',
        ]);

        if ($errors) {
            $request->flash();
            redirect_with('error', $this->firstError($errors), '/assets/create');
        }

        $categoryId = (int)$request->post('category_id');

        $data = [
            'asset_code'          => trim($request->post('asset_code')),
            'asset_name'          => trim($request->post('asset_name')),
            'category_id'         => $categoryId ?: null,
            'brand'               => trim($request->post('brand')),
            'model'               => trim($request->post('model')),
'serial_number'       => trim($request->post('serial_number')) ?: null,
            'ip_address'          => trim($request->post('ip_address')) ?: null,
            'product_number'      => trim($request->post('product_number')),
            'specification'       => trim($request->post('specification')),
            'purchase_date'       => $request->post('purchase_date') ?: null,
            'purchase_price'      => (float)$request->post('purchase_price'),
            'warranty_expiration'=> $request->post('warranty_expiration') ?: null,
            'supplier'            => trim($request->post('supplier')),
            'invoice_number'      => trim($request->post('invoice_number')),
            'building'            => trim($request->post('building')),
            'floor'               => trim($request->post('floor')),
            'room'                => trim($request->post('room')),
            'storage_location'    => trim($request->post('storage_location')),
            'department_id'       => $request->post('department_id') ?: null,
            'status'              => $request->post('status') ?: 'Available',
            'condition'           => $request->post('condition') ?: 'Good',
            'current_employee_id' => null,
            'photo'               => null,
            'notes'               => trim($request->post('notes')),
            'created_by'          => \App\Core\Auth::id(),
        ];

        $photo = $this->uploadPhoto($request->file('photo'));
        if (has_flash_error()) {
            redirect('/assets/create');
        }
        $data['photo'] = $photo;

        Database::transaction(function () use ($data, $request) {
            $id = Asset::create($data);
            $asset = Asset::findOrFail($id);
            $asset['id'] = $id;
            $asset['category_id'] = $data['category_id'];
            $asset->recordTransaction('Created', 'Asset created', "Asset {$data['asset_name']} registered.", \App\Core\Auth::id());
            $asset->recordTransaction('Purchase', 'Purchased', trim($request->post('supplier')) ? 'Supplier: ' . $request->post('supplier') : '', \App\Core\Auth::id());
            $asset->recordTransaction('Received', 'Received by IT', 'Received into IT inventory.', \App\Core\Auth::id());
            Audit::log('Asset Creation', "Created asset {$data['asset_code']} ({$data['asset_name']}).");
        });

        redirect_with('success', "Asset {$data['asset_code']} created.", '/assets');
    }

    public function show(Request $request, int $id): void
    {
        $asset = Asset::findOrFail($id);
        $history = Transaction::historyForAsset($id);
        $assignments = Database::select(
            "SELECT * FROM asset_assignments WHERE asset_id = ? ORDER BY assignment_date DESC", [$id]
        );
        $loans = Loan::where(['asset_id' => $id], 'loan_date', 'DESC');
        $transfers = Database::select(
            "SELECT * FROM asset_transfers WHERE asset_id = ? ORDER BY transfer_date DESC", [$id]
        );
        $maintenance = Database::select(
            "SELECT * FROM asset_maintenance WHERE asset_id = ? ORDER BY started_at DESC", [$id]
        );

        // Overdue detection for photos / timeline is already in Loan::derivedStatus.
        $canAssign = Asset::canBeIssued($asset);

        View::render('assets/show', [
            'pageTitle'  => $asset['asset_code'],
            'pageModule' => 'assets',
            'asset'      => $asset,
            'history'    => $history,
            'assignments'=> $assignments,
            'loans'      => $loans,
            'transfers'  => $transfers,
'maintenance'=> $maintenance,
            'canAssign'  => $canAssign,
            'statuses'   => Asset::STATUSES,
            'employee'   => $asset->holder(),
            'category'   => $asset->category(),
            'department' => $asset->department(),
        ]);
    }

    public function edit(Request $request, int $id): void
    {
        $asset = Asset::findOrFail($id);
        View::render('assets/form', [
            'pageTitle'  => 'Edit Asset - ' . $asset['asset_code'],
            'pageModule' => 'assets',
            'asset'      => $asset,
            'categories' => Category::all('name'),
            'departments'=> Department::all('name'),
            'statuses'   => Asset::STATUSES,
            'conditions' => Asset::CONDITIONS,
            'employees'  => Employee::where(['status' => 'Active'], 'name'),
            'nextCode'   => $asset['asset_code'],
        ]);
    }

    public function update(Request $request, int $id): void
    {
        $asset = Asset::findOrFail($id);

        $validator = new Validator();
        $errors = $validator->validate($request->all(), [
            'asset_code'   => "required|unique:assets,asset_code,$id",
            'asset_name'   => 'required|max:120',
            'category_id'  => 'required',
            'serial_number'=> "unique:assets,serial_number,$id",
            'purchase_date'=> 'date',
            'warranty_expiration' => 'date',
        ]);
        if ($errors) {
            redirect_with('error', $this->firstError($errors), "/assets/$id/edit");
        }

        $data = [
            'asset_code'          => trim($request->post('asset_code')),
            'asset_name'          => trim($request->post('asset_name')),
            'category_id'         => (int)$request->post('category_id') ?: null,
            'brand'               => trim($request->post('brand')),
            'model'               => trim($request->post('model')),
'serial_number'       => trim($request->post('serial_number')) ?: null,
            'ip_address'          => trim($request->post('ip_address')) ?: null,
            'product_number'      => trim($request->post('product_number')),
            'specification'       => trim($request->post('specification')),
            'purchase_date'       => $request->post('purchase_date') ?: null,
            'purchase_price'      => (float)$request->post('purchase_price'),
            'warranty_expiration'=> $request->post('warranty_expiration') ?: null,
            'supplier'            => trim($request->post('supplier')),
            'invoice_number'      => trim($request->post('invoice_number')),
            'building'            => trim($request->post('building')),
            'floor'               => trim($request->post('floor')),
            'room'                => trim($request->post('room')),
            'storage_location'    => trim($request->post('storage_location')),
            'department_id'       => $request->post('department_id') ?: null,
            'condition'           => $request->post('condition') ?: 'Good',
            'notes'               => trim($request->post('notes')),
            'updated_by'          => \App\Core\Auth::id(),
        ];

        // Master data fields never allowed to mutate while asset is in use.
        if (in_array($asset['status'], ['Assigned', 'On Loan'], true)) {
            unset($data['status']);
        } else {
            $data['status'] = $request->post('status') ?: $asset['status'];
        }

        $photo = $this->uploadPhoto($request->file('photo'), $asset['photo']);
        if (has_flash_error()) {
            redirect("/assets/$id/edit");
        }
        if ($photo) {
            $data['photo'] = $photo;
        }

        Database::transaction(function () use ($id, $data, $asset) {
            Asset::update($id, $data);
            $updated = Asset::findOrFail($id);
            $updated->recordTransaction('Updated', 'Asset details updated', '', \App\Core\Auth::id());
            Audit::log('Asset Update', "Updated asset {$data['asset_code']}.");
        });

        redirect_with('success', "Asset {$data['asset_code']} updated.", "/assets/$id");
    }

    public function destroy(Request $request, int $id): void
    {
        $asset = Asset::findOrFail($id);
        if (in_array($asset['status'], ['Assigned', 'On Loan'], true)) {
            redirect_with('error', 'Cannot delete an asset that is currently assigned or on loan.', "/assets/{$id}");
        }

Database::transaction(function () use ($id, $asset) {
            if ($asset['photo']) {
                $dir = App::instance()->path('uploads') . '/photos';
                $old = $dir . '/' . basename($asset['photo']);
                if (is_file($old)) {
                    @unlink($old);
                }
            }
            Asset::delete($id);
            Audit::log('Asset Deletion', "Deleted asset {$asset['asset_code']}.");
        });

        redirect_with('success', 'Asset deleted.', '/assets');
    }

public function clone(Request $request, int $id): void
    {
        $source = Asset::findOrFail($id);

        $photo = null;
        if ($source['photo']) {
            $ext = pathinfo($source['photo'], PATHINFO_EXTENSION);
            $name = date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $dir = App::instance()->path('uploads') . '/photos';
            $src = $dir . '/' . basename($source['photo']);
            if (is_file($src) && copy($src, $dir . '/' . $name)) {
                $photo = 'uploads/photos/' . $name;
            }
        }

        $data = [
            'asset_code'          => Asset::nextCode((int)$source['category_id']),
            'asset_name'          => $source['asset_name'],
            'category_id'         => $source['category_id'],
            'brand'               => $source['brand'],
            'model'               => $source['model'],
            'serial_number'       => null,
            'ip_address'          => null,
            'product_number'      => $source['product_number'],
            'specification'       => $source['specification'],
            'purchase_date'       => $source['purchase_date'],
            'purchase_price'      => (float)$source['purchase_price'],
            'warranty_expiration'=> $source['warranty_expiration'],
            'supplier'            => $source['supplier'],
            'invoice_number'      => $source['invoice_number'],
            'building'            => $source['building'],
            'floor'               => $source['floor'],
            'room'                => $source['room'],
            'storage_location'    => $source['storage_location'],
            'department_id'       => $source['department_id'],
            'status'              => Asset::STATUS_AVAILABLE,
            'condition'           => $source['condition'],
            'current_employee_id' => null,
            'photo'               => $photo,
            'notes'               => $source['notes'],
            'created_by'          => \App\Core\Auth::id(),
        ];

        $newId = null;
        Database::transaction(function () use ($data, $source, &$newId) {
            $newId = Asset::create($data);
            $asset = Asset::findOrFail($newId);
            $asset->recordTransaction(
                'Created',
                'Asset created',
                "Cloned from {$source['asset_code']} ({$source['asset_name']}).",
                \App\Core\Auth::id()
            );
            Audit::log('Asset Clone', "Cloned asset {$source['asset_code']} as {$data['asset_code']}.");
        });

        redirect_with('success', "Asset cloned as {$data['asset_code']}.", "/assets/{$newId}");
    }

    public function updateStatus(Request $request, int $id): void
    {
        $asset = Asset::findOrFail($id);
        $newStatus = $request->post('status');
        $note = trim($request->post('note'));

        if (!in_array($newStatus, Asset::STATUSES, true)) {
            redirect_with('error', 'Invalid status.', "/assets/{$id}");
        }

        if (in_array($asset['status'], ['Assigned'], true) && $newStatus !== 'Assigned') {
            redirect_with('error', 'Release the assignment first through the Reassign / Release action.', "/assets/{$id}");
        }
        if ($asset['status'] === 'On Loan' && $newStatus !== 'On Loan') {
            redirect_with('error', 'Process the loan return first.', "/assets/{$id}");
        }

        Database::transaction(function () use ($id, $asset, $newStatus, $note) {
            $oldStatus = $asset['status'];
            Asset::update($id, ['status' => $newStatus, 'updated_by' => \App\Core\Auth::id()]);
            $a = Asset::findOrFail($id);
            $a->recordTransaction('Status Change', "Status changed: {$oldStatus} → {$newStatus}", $note, \App\Core\Auth::id());

            $title = match ($newStatus) {
                'Under Maintenance' => 'Asset under maintenance',
                'Damaged'           => 'Asset damage reported',
                'Lost'              => 'Asset reported lost',
                'Retired'           => 'Asset retired',
                'Disposed'          => 'Asset disposed',
                default             => 'Asset status updated',
            };
            Notification::push('status', $title, "{$asset['asset_code']} ({$asset['asset_name']}) is now {$newStatus}.");
            Audit::log('Asset Status Change', "{$asset['asset_code']} status {$oldStatus} → {$newStatus}.");
        });

        redirect_with('success', "Status updated to {$newStatus}.", "/assets/{$id}");
    }

    public function export(): void
    {
        $assets = Database::select(
            "SELECT a.*, c.name category_name, d.name department_name, e.name employee_name
             FROM assets a
             LEFT JOIN asset_categories c ON c.id = a.category_id
             LEFT JOIN departments d ON d.id = a.department_id
             LEFT JOIN employees e ON e.id = a.current_employee_id
             ORDER BY a.asset_code ASC"
        );

        $headers = ['Asset Code', 'Asset Name', 'Category', 'Brand', 'Model', 'Serial Number', 'Status', 'Condition', 'Current Employee', 'Department', 'Building', 'Floor', 'Room', 'Purchase Date', 'Purchase Price', 'Warranty Expiration', 'Supplier'];
        $rows = [];
        foreach ($assets as $a) {
            $rows[] = [
                $a['asset_code'], $a['asset_name'], $a['category_name'], $a['brand'], $a['model'],
                $a['serial_number'], $a['status'], $a['condition'], $a['employee_name'] ?? '', $a['department_name'] ?? '',
                $a['building'], $a['floor'], $a['room'], $a['purchase_date'], (float)$a['purchase_price'],
                $a['warranty_expiration'], $a['supplier'],
            ];
        }

        Excel::download('Inventory.xlsx', $headers, $rows, 'Inventory');
    }

    private function firstError(array $errors): string
    {
        return reset($errors);
    }
}

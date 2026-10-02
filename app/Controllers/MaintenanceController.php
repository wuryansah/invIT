<?php

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Database;
use App\Core\Request;
use App\Core\Validator;
use App\Core\View;
use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\Notification;

class MaintenanceController extends BaseController
{
    private const PER_PAGE = 20;

    public function index(Request $request): void
    {
        $sql = "SELECT m.*, a.asset_code, a.asset_name, a.condition asset_condition, u.name created_by_name
                FROM asset_maintenance m
                LEFT JOIN assets a ON a.id = m.asset_id
                LEFT JOIN users u ON u.id = m.created_by
                WHERE 1";
        $bind = [];

        if ($f = $request->get('status')) {
            $sql .= " AND m.status = ?";
            $bind[] = $f;
        }
        if ($f = $request->get('q')) {
            $sql .= " AND (m.maintenance_no LIKE ? OR a.asset_code LIKE ? OR a.asset_name LIKE ?)";
            $term = '%' . $f . '%';
            array_push($bind, $term, $term, $term);
        }

        $sql .= " ORDER BY m.started_at DESC, m.id DESC";
        $paginated = Maintenance::paginate($sql, $bind, self::PER_PAGE, (int)$request->get('page', 1));

        $assetsInMaintenance = Database::select(
            "SELECT a.*, c.name category_name FROM assets a
             LEFT JOIN asset_categories c ON c.id = a.category_id
             WHERE a.status = 'Under Maintenance' ORDER BY a.asset_code"
        );

        View::render('maintenance/index', [
            'pageTitle'  => 'Maintenance',
            'pageModule' => 'assets',
            'pagination' => $paginated,
            'assetsInMaintenance' => $assetsInMaintenance,
            'statusFilter' => $request->get('status'),
            'q'          => $request->get('q'),
        ]);
    }

    public function store(Request $request): void
    {
        $errors = (new Validator())->validate($request->all(), [
            'asset_id'   => 'required',
            'started_at' => 'required|date',
            'type'       => 'required|max:100',
        ]);
        if ($errors) {
            redirect_with('error', $this->firstError($errors), '/maintenance');
        }

        $asset = Asset::findOrFail((int)$request->post('asset_id'));

        Database::transaction(function () use ($asset, $request) {
            $no = Maintenance::nextNumber();
            Maintenance::create([
                'maintenance_no' => $no,
                'asset_id'       => $asset['id'],
                'started_at'     => $request->post('started_at'),
                'type'           => trim($request->post('type')),
                'description'    => trim($request->post('description')),
                'cost'           => (float)$request->post('cost'),
                'performed_by'   => trim($request->post('performed_by')),
                'status'         => 'In Progress',
                'notes'          => trim($request->post('notes')),
                'created_by'     => \App\Core\Auth::id(),
            ]);

            Asset::update($asset['id'], ['status' => Asset::STATUS_MAINTENANCE]);

            $updated = Asset::findOrFail($asset['id']);
            $updated->recordTransaction('Maintenance', 'Under maintenance', trim($request->post('description')) ?: trim($request->post('type')), \App\Core\Auth::id());

            Notification::push('maintenance', 'Maintenance started', "{$asset['asset_code']} ({$asset['asset_name']}) is under maintenance.");
            Audit::log('Maintenance', "Opened maintenance ticket {$no} for {$asset['asset_code']}.");
        });

        redirect_with('success', "Maintenance record created. {$asset['asset_code']} is now Under Maintenance.", '/maintenance');
    }

    public function complete(Request $request, int $id): void
    {
        $mnt = Maintenance::findOrFail($id);
        $asset = Asset::findOrFail((int)$mnt['asset_id']);

        if ($mnt['status'] === 'Completed') {
            redirect_with('error', 'This maintenance is already completed.', '/maintenance');
        }

        Database::transaction(function () use ($mnt, $asset, $id, $request) {
            Maintenance::update($id, [
                'status'       => 'Completed',
                'completed_at' => $request->post('completed_at') ?: today(),
                'cost'         => (float)($request->post('cost') ?? $mnt['cost']),
                'notes'        => trim($request->post('notes')),
            ]);

            Asset::update($asset['id'], ['status' => Asset::STATUS_AVAILABLE]);
            $updated = Asset::findOrFail($asset['id']);
            $updated->recordTransaction('Maintenance', 'Maintenance completed', trim($request->post('notes')), \App\Core\Auth::id());

            Notification::push('maintenance', 'Maintenance completed', "{$asset['asset_code']} is back and Available.");
            Audit::log('Maintenance', "Completed maintenance {$mnt['maintenance_no']} for {$asset['asset_code']}.");
        });

        redirect_with('success', "Maintenance completed. {$asset['asset_code']} is Available again.", '/maintenance');
    }

    private function firstError(array $errors): string
    {
        return reset($errors);
    }
}
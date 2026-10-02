<?php

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Database;
use App\Core\Request;
use App\Core\Validator;
use App\Core\View;
use App\Models\Adjustment;
use App\Models\Asset;
use App\Models\Notification;

class AdjustmentController extends BaseController
{
    public function index(Request $request): void
    {
        $sql = "SELECT g.*, a.asset_code, a.asset_name, u.name performed_by_name
                FROM inventory_adjustments g
                LEFT JOIN assets a ON a.id = g.asset_id
                LEFT JOIN users u ON u.id = g.performed_by
                WHERE 1";
        $bind = [];
        if ($f = $request->get('type')) {
            $sql .= " AND g.type = ?";
            $bind[] = $f;
        }
        $sql .= " ORDER BY g.created_at DESC, g.id DESC";
        $paginated = Adjustment::paginate($sql, $bind, 20, (int)$request->get('page', 1));

        $assets = Asset::all('asset_code');

        View::render('adjustments/index', [
            'pageTitle'  => 'Inventory Adjustments',
            'pageModule' => 'settings',
            'pagination' => $paginated,
            'assets'     => $assets,
            'typeFilter' => $request->get('type'),
        ]);
    }

    public function store(Request $request): void
    {
        $errors = (new Validator())->validate($request->all(), [
            'asset_id'  => 'required',
            'type'      => 'required|in:Stock Opname,Found,Missing,Quantity Correction,Relocation,Data Correction',
            'reason'    => 'required|max:255',
        ]);
        if ($errors) {
            redirect_with('error', $this->firstError($errors), '/adjustments');
        }

        $asset = Asset::findOrFail((int)$request->post('asset_id'));
        $type = $request->post('type');
        $newStatus = $request->post('new_status') ?: $asset['status'];
        $newLocation = trim($request->post('new_location'));

        Database::transaction(function () use ($asset, $type, $newStatus, $newLocation, $request) {
            $no = Adjustment::nextNumber();
            $fullOld = $asset['building'] . '/' . $asset['floor'] . '/' . $asset['room'] . ($asset['storage_location'] ? ' (' . $asset['storage_location'] . ')' : '');
            $fullNew = $newLocation ?: '';

            Adjustment::create([
                'adjustment_no' => $no,
                'asset_id'      => $asset['id'],
                'type'          => $type,
                'qty_before'    => 1,
                'qty_change'    => 0,
                'qty_after'     => 1,
                'old_status'    => $asset['status'],
                'new_status'    => $newStatus,
                'old_location'  => trim($fullOld),
                'new_location'  => $fullNew,
                'reason'        => trim($request->post('reason')),
                'notes'         => trim($request->post('notes')),
                'performed_by'  => \App\Core\Auth::id(),
            ]);

            $updates = [];
            if ($newStatus !== $asset['status']) {
                $updates['status'] = $newStatus;
            }
            if ($newLocation) {
                // Support "Building / Floor / Room (storage)" shorthand.
                $parts = array_map('trim', explode('/', $newLocation));
                $updates['building'] = $parts[0] ?? '';
                $updates['floor'] = $parts[1] ?? '';
                $updates['room'] = $parts[2] ?? '';
            }
            if ($updates) {
                Asset::update($asset['id'], $updates);
            }

            $label = match ($type) {
                'Found'        => 'Found during stock opname',
                'Missing'      => 'Marked missing during stock opname',
                'Relocation'   => 'Relocated storage location',
                'Data Correction' => 'Master data corrected',
                default        => 'Inventory adjustment',
            };
            $updated = Asset::findOrFail($asset['id']);
            $updated->recordTransaction('Adjustment', $label, trim($request->post('reason')) . ' (' . $no . ')', \App\Core\Auth::id());

            if ($type === 'Missing') {
                Notification::push('adjustment', 'Asset missing', "{$asset['asset_code']} ({$asset['asset_name']}) marked as missing.");
                Audit::log('Inventory Adjustment', "{$asset['asset_code']} marked Missing ({$no}).");
            } else {
                Notification::push('adjustment', 'Inventory adjusted', "Adjustment {$no} applied to {$asset['asset_code']}.");
                Audit::log('Inventory Adjustment', "Adjustment {$no} applied to {$asset['asset_code']} (type {$type}).");
            }
        });

        redirect_with('success', 'Adjustment recorded.', '/adjustments');
    }

    private function firstError(array $errors): string
    {
        return reset($errors);
    }
}
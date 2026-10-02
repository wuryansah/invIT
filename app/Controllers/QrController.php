<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\View;

class QrController extends BaseController
{
    public function scan(): void
    {
        View::render('qr/scan', [
            'pageTitle'  => 'Scan QR Code',
            'pageModule' => 'assets',
        ]);
    }

    /** Resolve a scanned asset code and render the "public" asset view. */
    public function lookup(Request $request, string $code): void
    {
        $code = strtoupper(trim($code));
        $asset = Database::first(
            "SELECT a.*, c.name category_name, d.name department_name, e.name employee_name, e.position, e.employee_number
             FROM assets a
             LEFT JOIN asset_categories c ON c.id = a.category_id
             LEFT JOIN departments d ON d.id = a.department_id
             LEFT JOIN employees e ON e.id = a.current_employee_id
             WHERE a.asset_code = ?",
            [$code]
        );

        if (!$asset) {
            app_response()->abort(404, "No asset found with code {$code}.");
        }

        View::render('qr/lookup', [
            'pageTitle' => 'Asset ' . $asset['asset_code'],
            'pageModule'=> 'assets',
            'asset'     => $asset,
            'isScan'    => true,
        ]);
    }
}
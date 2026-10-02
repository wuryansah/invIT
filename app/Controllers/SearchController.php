<?php

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Database;
use App\Core\Request;
use App\Core\View;

class SearchController extends BaseController
{
    public function index(Request $request): void
    {
        $q = trim((string)$request->get('q'));

        $assets = [];
        $employees = [];

        if ($q !== '') {
            $assets = Database::select(
                "SELECT a.*, c.name category_name, d.name department_name, e.name employee_name
                 FROM assets a
                 LEFT JOIN asset_categories c ON c.id = a.category_id
                 LEFT JOIN departments d ON d.id = a.department_id
                 LEFT JOIN employees e ON e.id = a.current_employee_id
                 WHERE a.asset_code LIKE ? OR a.asset_name LIKE ? OR a.serial_number LIKE ?
                       OR a.brand LIKE ? OR a.model LIKE ? OR a.product_number LIKE ?
                       OR e.name LIKE ? OR d.name LIKE ?
                 ORDER BY a.asset_code LIMIT 50",
                array_fill(0, 8, '%' . $q . '%')
            );

            $employees = Database::select(
                "SELECT e.*, d.name department_name FROM employees e
                 LEFT JOIN departments d ON d.id = e.department_id
                 WHERE e.name LIKE ? OR e.employee_number LIKE ? OR e.email LIKE ?
                       OR e.position LIKE ? OR d.name LIKE ?
                 ORDER BY e.name LIMIT 50",
                array_fill(0, 5, '%' . $q . '%')
            );

            Audit::log('Search', "Global search for query: {$q}");
        }

        View::render('search/index', [
            'pageTitle'  => 'Search',
            'pageModule' => 'search',
            'q'          => $q,
            'assets'     => $assets,
            'employees'  => $employees,
        ]);
    }
}
<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\View;
use App\Models\AuditLog;
use App\Models\User;

class AuditController extends BaseController
{
    public function index(Request $request): void
    {
        $filters = [
            'user'   => $request->get('user'),
            'action' => $request->get('action'),
            'from'   => $request->get('from'),
            'to'     => $request->get('to'),
        ];

        $paginated = AuditLog::allWithUser(25, (int)$request->get('page', 1), $filters);

        View::render('audit/index', [
            'pageTitle'  => 'Audit Log',
            'pageModule' => 'settings',
            'pagination' => $paginated,
            'filters'    => $filters,
            'users'      => User::all('name'),
        ]);
    }
}
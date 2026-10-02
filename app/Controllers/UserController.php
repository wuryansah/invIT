<?php

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Request;
use App\Core\Validator;
use App\Core\View;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;

class UserController extends BaseController
{
    public function index(Request $request): void
    {
        $sql = "SELECT u.*, e.name employee_name FROM users u LEFT JOIN employees e ON e.id = u.employee_id WHERE 1";
        $bind = [];
        if ($f = $request->get('q')) {
            $sql .= " AND (u.name LIKE ? OR u.email LIKE ?)";
            $term = '%' . $f . '%';
            array_push($bind, $term, $term);
        }
        $sql .= " ORDER BY u.created_at DESC, u.id DESC";
        $paginated = User::paginate($sql, $bind, 20, (int)$request->get('page', 1));

        View::render('users/index', [
            'pageTitle'  => 'System Users',
            'pageModule' => 'settings',
            'pagination' => $paginated,
            'q'          => $request->get('q'),
        ]);
    }

    public function create(): void
    {
        View::render('users/form', [
            'pageTitle'  => 'New User',
            'pageModule' => 'settings',
            'user'       => null,
            'employees'  => Employee::all('name'),
        ]);
    }

    public function store(Request $request): void
    {
        $errors = (new Validator())->validate($request->all(), [
            'name'     => 'required|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role'     => 'required|in:admin,staff,employee',
        ]);
        if ($errors) {
            $request->flash();
            redirect_with('error', $this->firstError($errors), '/users/create');
        }

        $id = User::create([
            'name'        => trim($request->post('name')),
            'email'       => trim($request->post('email')),
            'password'    => password_hash($request->post('password'), PASSWORD_DEFAULT),
            'role'        => $request->post('role'),
            'employee_id' => $request->post('employee_id') ?: null,
            'is_active'   => 1,
        ]);

        Audit::log('User Creation', "Created user account for {$request->post('name')}.");
        redirect_with('success', 'User created.', "/users/$id/edit");
    }

    public function edit(Request $request, int $id): void
    {
        $user = User::findOrFail($id);
        View::render('users/form', [
            'pageTitle'  => 'Edit User - ' . $user['name'],
            'pageModule' => 'settings',
            'user'       => $user,
            'employees'  => Employee::all('name'),
        ]);
    }

    public function update(Request $request, int $id): void
    {
        $user = User::findOrFail($id);
        $errors = (new Validator())->validate($request->all(), [
            'name'  => 'required|max:100',
            'email' => "required|email|unique:users,email,$id",
            'password' => 'min:8',
            'role'  => 'required|in:admin,staff,employee',
        ]);
        if ($errors) {
            redirect_with('error', $this->firstError($errors), "/users/$id/edit");
        }

        $data = [
            'name'        => trim($request->post('name')),
            'email'       => trim($request->post('email')),
            'role'        => $request->post('role'),
            'employee_id' => $request->post('employee_id') ?: null,
            'is_active'   => $request->post('is_active') ? 1 : 0,
        ];
        if (!empty($request->post('password'))) {
            $data['password'] = password_hash($request->post('password'), PASSWORD_DEFAULT);
        }

        User::update($id, $data);
        Audit::log('User Update', "Updated user account for {$data['name']}.");
        redirect_with('success', 'User updated.', "/users/$id/edit");
    }

    public function toggle(Request $request, int $id): void
    {
        $user = User::findOrFail($id);
        if ($user['id'] === \App\Core\Auth::id()) {
            redirect_with('error', 'You cannot deactivate your own account.', '/users');
        }
        $status = $user['is_active'] ? 0 : 1;
        User::update($id, ['is_active' => $status]);
        Audit::log('User Change', ($status ? 'Activated' : 'Deactivated') . " user {$user['name']}.");
        redirect_with('success', 'User account ' . ($status ? 'activated' : 'deactivated') . '.', '/users');
    }

    private function firstError(array $errors): string
    {
        return reset($errors);
    }
}
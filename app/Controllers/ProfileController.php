<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Audit;
use App\Core\Database;
use App\Core\Request;
use App\Core\Validator;
use App\Core\View;
use App\Models\Asset;
use App\Models\Employee;
use App\Models\Loan;
use App\Models\Notification;
use App\Models\Transaction;
use App\Models\User;

class ProfileController extends BaseController
{
    public function index(): void
    {
        View::render('profile/index', [
            'pageTitle'  => 'My Profile',
            'pageModule' => 'profile',
            'user'       => Auth::user(),
        ]);
    }

    public function updatePassword(Request $request): void
    {
        $validator = new Validator();
        $errors = $validator->validate($request->all(), [
            'current_password'      => 'required',
            'new_password'          => 'required|min:8',
            'new_password_confirmation' => 'required',
        ]);

        $user = Auth::user();
        if (!$errors && ($request->post('new_password') !== $request->post('new_password_confirmation'))) {
            $errors['new_password_confirmation'] = 'Password confirmation does not match.';
        }
        if (!$errors && !password_verify($request->post('current_password'), $user['password'])) {
            $errors['current_password'] = 'Current password is incorrect.';
        }

        if ($errors) {
            foreach ($errors as $field => $message) {
                $_SESSION['_errors'][$field] = $message;
            }
            back();
        }

        User::update((int)$user['id'], ['password' => password_hash($request->post('new_password'), PASSWORD_DEFAULT)]);
        Audit::log('Password Change', "User {$user['name']} changed own password.");
        redirect_with('success', 'Password updated successfully.', '/profile');
    }

    /** Employee self-service page: what is assigned / loaned to the user. */
    public function myAssets(): void
    {
        $user = Auth::user();
        $employee = Auth::employee();

        if (!$employee) {
            View::render('profile/my-assets', [
                'pageTitle' => 'My Equipment',
                'pageModule'=> 'my-assets',
                'employee'  => null,
                'assigned'  => [],
                'loans'     => [],
                'history'   => [],
            ]);
        }

        $assigned = Asset::where(['current_employee_id' => $employee['id'], 'status' => 'Assigned'], 'asset_code');
        $loans = Loan::where(['employee_id' => $employee['id'], 'status' => 'Active'], 'loan_date', 'DESC');
        $history = Database::select(
            "SELECT a.asset_code, a.asset_name, t.*, u.name user_name
             FROM asset_transactions t
             LEFT JOIN assets a ON a.id = t.asset_id
             LEFT JOIN users u ON u.id = t.user_id
             WHERE t.asset_id IN (
                SELECT asset_id FROM asset_assignments WHERE employee_id = ?
                UNION
                SELECT asset_id FROM asset_loans WHERE employee_id = ?
             )
             ORDER BY t.created_at DESC, t.id DESC LIMIT 100",
            [$employee['id'], $employee['id']]
        );

        View::render('profile/my-assets', [
            'pageTitle' => 'My Equipment',
            'pageModule'=> 'my-assets',
            'employee'  => $employee,
            'assigned'  => $assigned,
            'loans'     => $loans,
            'history'   => $history,
        ]);
    }
}
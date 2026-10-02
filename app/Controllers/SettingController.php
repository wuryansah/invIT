<?php

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Request;
use App\Core\Validator;
use App\Core\View;
use App\Models\Category;
use App\Models\Department;
use App\Models\Setting;

class SettingController extends BaseController
{
    public function index(): void
    {
        View::render('settings/index', [
            'pageTitle'   => 'System Settings',
            'pageModule'  => 'settings',
            'departments' => Department::all('name'),
            'categories'  => Category::all('name'),
            'settings'    => [
                'company_name'         => setting('company_name', 'PT Example Company'),
                'loan_due_soon_days'   => setting('loan_due_soon_days', '2'),
                'notification_enabled' => setting('notification_enabled', '1'),
            ],
        ]);
    }

    public function store(Request $request): void
    {
        $errors = (new Validator())->validate($request->all(), [
            'company_name'       => 'required|max:120',
            'loan_due_soon_days' => 'required|integer|min:0',
        ]);
        if ($errors) {
            redirect_with('error', reset($errors), '/settings');
        }

        Setting::set('company_name', trim($request->post('company_name')));
        Setting::set('loan_due_soon_days', (int)$request->post('loan_due_soon_days'));
        Setting::set('notification_enabled', $request->post('notification_enabled') ? '1' : '0');

        Audit::log('Settings Update', 'General settings updated.');
        redirect_with('success', 'Settings saved.', '/settings');
    }

    public function storeDepartment(Request $request): void
    {
        $errors = (new Validator())->validate($request->all(), [
            'name' => 'required|max:100|unique:departments,name',
            'code' => 'max:20',
        ]);
        if ($errors) {
            redirect_with('error', reset($errors), '/settings');
        }

        Department::create([
            'name'        => trim($request->post('name')),
            'code'        => trim($request->post('code')) ?: null,
            'description' => trim($request->post('description')),
        ]);
        Audit::log('Department Creation', "Created department {$request->post('name')}.");
        redirect_with('success', 'Department added.', '/settings');
    }

    public function deleteDepartment(Request $request, int $id): void
    {
        $dept = Department::findOrFail($id);
        $inUse = Department::employeeCount($id) + Department::assetCount($id);
        if ($inUse > 0) {
            redirect_with('error', 'Department is still used by employees or assets.', '/settings');
        }
        Department::delete($id);
        Audit::log('Department Deletion', "Deleted department {$dept['name']}.");
        redirect_with('success', 'Department deleted.', '/settings');
    }

    public function storeCategory(Request $request): void
    {
        $errors = (new Validator())->validate($request->all(), [
            'name' => 'required|max:100|unique:asset_categories,name',
            'code' => 'required|max:20',
        ]);
        if ($errors) {
            redirect_with('error', reset($errors), '/settings');
        }

        Category::create([
            'name'        => trim($request->post('name')),
            'code'        => strtoupper(trim($request->post('code'))),
            'description' => trim($request->post('description')),
        ]);
        Audit::log('Category Creation', "Created asset category {$request->post('name')}.");
        redirect_with('success', 'Category added.', '/settings');
    }

    public function deleteCategory(Request $request, int $id): void
    {
        $category = Category::findOrFail($id);
        if (Category::assetCount($id) > 0) {
            redirect_with('error', 'Category still has assets. Move them first.', '/settings');
        }
        Category::delete($id);
        Audit::log('Category Deletion', "Deleted category {$category['name']}.");
        redirect_with('success', 'Category deleted.', '/settings');
    }
}
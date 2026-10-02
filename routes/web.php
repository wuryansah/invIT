<?php

/** @var App\Core\Router $router */

/*
 * Guard legend: guest | auth | employee | staff | admin
 */

// ---- Authentication -----------------------------------------------------
$router->get('/login', 'AuthController@loginForm', 'guest');
$router->post('/login', 'AuthController@login', 'guest');
$router->post('/logout', 'AuthController@logout', 'auth');

// ---- General ------------------------------------------------------------
$router->get('/', 'DashboardController@index', 'auth');
$router->get('/profile', 'ProfileController@index', 'auth');
$router->post('/profile/password', 'ProfileController@updatePassword', 'auth');

// ---- Employee self service ----------------------------------------------
$router->get('/my-assets', 'ProfileController@myAssets', 'auth');

// ---- Assets --------------------------------------------------------------
$router->get('/assets', 'AssetController@index', 'staff');
$router->get('/assets/create', 'AssetController@create', 'staff');
$router->post('/assets', 'AssetController@store', 'staff');
$router->get('/assets/{id}', 'AssetController@show', 'auth');
$router->get('/assets/{id}/edit', 'AssetController@edit', 'staff');
$router->post('/assets/{id}', 'AssetController@update', 'staff');
$router->post('/assets/{id}/delete', 'AssetController@destroy', 'admin');
$router->post('/assets/{id}/status', 'AssetController@updateStatus', 'staff');
$router->post('/assets/{id}/clone', 'AssetController@clone', 'staff');
$router->get('/assets/export', 'AssetController@export', 'staff');

// ---- Employees ------------------------------------------------------------
$router->get('/employees', 'EmployeeController@index', 'staff');
$router->get('/employees/create', 'EmployeeController@create', 'staff');
$router->post('/employees', 'EmployeeController@store', 'staff');
$router->get('/employees/{id}', 'EmployeeController@show', 'staff');
$router->get('/employees/{id}/edit', 'EmployeeController@edit', 'staff');
$router->post('/employees/{id}', 'EmployeeController@update', 'staff');
$router->post('/employees/{id}/delete', 'EmployeeController@destroy', 'admin');

// ---- Assignments ----------------------------------------------------------
$router->get('/assignments', 'AssignmentController@index', 'staff');
$router->get('/assign/create', 'AssignmentController@create', 'staff');
$router->post('/assign', 'AssignmentController@store', 'staff');
$router->post('/assign/{id}/unassign', 'AssignmentController@unassign', 'staff');

// ---- Transfers ------------------------------------------------------------
$router->get('/transfers', 'TransferController@index', 'staff');
$router->get('/transfers/create', 'TransferController@create', 'staff');
$router->post('/transfers', 'TransferController@store', 'staff');

// ---- Loans ----------------------------------------------------------------
$router->get('/loans', 'LoanController@index', 'staff');
$router->get('/loans/create', 'LoanController@create', 'staff');
$router->post('/loans', 'LoanController@store', 'staff');
$router->get('/loans/{id}', 'LoanController@show', 'staff');
$router->post('/loans/{id}/return', 'LoanController@processReturn', 'staff');

// ---- Maintenance ----------------------------------------------------------
$router->get('/maintenance', 'MaintenanceController@index', 'staff');
$router->post('/maintenance', 'MaintenanceController@store', 'staff');
$router->post('/maintenance/{id}/complete', 'MaintenanceController@complete', 'staff');

// ---- Reports & Export -----------------------------------------------------
$router->get('/reports', 'ReportController@index', 'staff');
$router->get('/reports/inventory', 'ReportController@inventory', 'staff');
$router->get('/reports/employee-assets', 'ReportController@employeeAssets', 'staff');
$router->get('/reports/loans', 'ReportController@loans', 'staff');
$router->get('/reports/department', 'ReportController@department', 'staff');
$router->get('/reports/maintenance', 'ReportController@maintenance', 'staff');
$router->get('/reports/asset-history/{id}', 'ReportController@assetHistory', 'staff');
$router->get('/reports/export/{type}', 'ReportController@export', 'staff');

// ---- Inventory adjustment & audit -----------------------------------------
$router->get('/adjustments', 'AdjustmentController@index', 'admin');
$router->post('/adjustments', 'AdjustmentController@store', 'admin');
$router->get('/audit', 'AuditController@index', 'admin');

// ---- Users (admin) ---------------------------------------------------------
$router->get('/users', 'UserController@index', 'admin');
$router->get('/users/create', 'UserController@create', 'admin');
$router->post('/users', 'UserController@store', 'admin');
$router->get('/users/{id}/edit', 'UserController@edit', 'admin');
$router->post('/users/{id}', 'UserController@update', 'admin');
$router->post('/users/{id}/toggle', 'UserController@toggle', 'admin');

// ---- Settings (admin: departments, categories, general) --------------------
$router->get('/settings', 'SettingController@index', 'admin');
$router->post('/settings', 'SettingController@store', 'admin');
$router->post('/settings/departments', 'SettingController@storeDepartment', 'admin');
$router->post('/settings/departments/{id}/delete', 'SettingController@deleteDepartment', 'admin');
$router->post('/settings/categories', 'SettingController@storeCategory', 'admin');
$router->post('/settings/categories/{id}/delete', 'SettingController@deleteCategory', 'admin');

// ---- Notifications ---------------------------------------------------------
$router->get('/notifications', 'NotificationController@index', 'auth');
$router->post('/notifications/read-all', 'NotificationController@readAll', 'auth');

// ---- QR / Barcode -----------------------------------------------------------
$router->get('/scan', 'QrController@scan', 'auth');
$router->get('/qr/{code}', 'QrController@lookup', 'auth');

// ---- Search ----------------------------------------------------------------
$router->get('/search', 'SearchController@index', 'staff');

// ---- Printable documents ---------------------------------------------------
$router->get('/print/handover-assignment/{id}', 'PrintController@handoverAssignment', 'staff');
$router->get('/print/handover-loan/{id}', 'PrintController@handoverLoan', 'staff');
$router->get('/print/return/{id}', 'PrintController@returnForm', 'staff');
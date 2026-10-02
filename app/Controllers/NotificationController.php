<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\View;
use App\Models\Notification;

class NotificationController extends BaseController
{
    public function index(): void
    {
        $notifications = Notification::forUser(null, 200);

        View::render('notifications/index', [
            'pageTitle'    => 'Notifications',
            'pageModule'   => 'dashboard',
            'notifications'=> $notifications,
        ]);
    }

    public function readAll(): void
    {
        $userId = \App\Core\Auth::id() ?: 0;
        Database::run("UPDATE notifications SET is_read = 1 WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0", [$userId]);
        redirect('/notifications');
    }
}
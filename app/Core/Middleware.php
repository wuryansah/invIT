<?php

namespace App\Core;

class Middleware
{
    /**
     * Authorize the current request based on the route guard.
     * Guard values: guest | auth | employee | staff | admin
     */
    public static function authorize(string $guard): void
    {
        switch ($guard) {
            case 'guest':
                if (Auth::check()) {
                    redirect('/');
                }
                return;

            case 'auth':
                if (!Auth::check()) {
                    redirect('/login');
                }
                return;

            case 'employee':
                if (!Auth::check()) {
                    redirect('/login');
                }
                return;

            case 'staff':
                if (!Auth::check()) {
                    redirect('/login');
                }
                if (!in_array(Auth::role(), ['admin', 'staff'], true)) {
                    self::forbidden();
                }
                return;

            case 'admin':
                if (!Auth::check()) {
                    redirect('/login');
                }
                if (Auth::role() !== 'admin') {
                    self::forbidden();
                }
                return;

            default:
                if (!Auth::check()) {
                    redirect('/login');
                }
        }
    }

    private static function forbidden(): never
    {
        app_response()->abort(403, 'You are not authorized to access this page.');
    }
}
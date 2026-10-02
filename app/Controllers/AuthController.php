<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Audit;
use App\Core\Request;
use App\Core\Validator;
use App\Core\View;

class AuthController extends BaseController
{
    public function loginForm(): void
    {
        View::render('auth/login', ['pageTitle' => 'Sign in'], null);
    }

    public function login(Request $request): void
    {
        $errors = (new Validator())->validate($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($errors) {
            $request->flash();
            redirect_with('error', $errors['email'] ?? $errors['password'] ?? 'Please check your input.', '/login');
        }

        if (!Auth::attempt($request->post('email'), (string)$request->post('password'))) {
            flash('error', 'Invalid credentials or inactive account.');
            redirect('/login');
        }

        $user = Auth::user();
        Audit::log('Login', "User {$user['name']} logged in.");

        if (Auth::role() === 'employee') {
            redirect('/my-assets');
        }
        redirect('/');
    }

    public function logout(): void
    {
        $user = Auth::user();
        if ($user) {
            Audit::log('Logout', "User {$user['name']} logged out.");
        }
        Auth::logout();
        redirect('/login');
    }
}
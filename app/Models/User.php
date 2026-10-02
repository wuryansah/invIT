<?php

namespace App\Models;

class User extends Model
{
    protected static string $table = 'users';
    protected static array $fillable = [
        'name', 'email', 'password', 'role', 'employee_id', 'is_active', 'last_login_at',
    ];
}
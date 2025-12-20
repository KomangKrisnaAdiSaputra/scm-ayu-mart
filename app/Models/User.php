<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'users';
    protected $primaryKey = 'users_id';

    protected $fillable = [
        'username',
        'password',
        'role',
        'nama',
        'email',
        'is_active'
    ];

    protected $hidden = ['password'];

    public function getAuthIdentifierName()
    {
        return 'username';
    }
}

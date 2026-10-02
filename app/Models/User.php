<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
//use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
   // use HasApiTokens;
    protected $table = 'users';
    protected $fillable = [
        'role_id',
        'username',
        'full_name',
        'phone_no',
        'nic',
        'password',
    ];

    protected $hidden = ['password'];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function loginSession()
{
    return $this->hasOne(LoginSession::class, 'user_id', 'id');
}
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $table = 'roles';
    protected $fillable = ['name'];
    public $timestamps = false; // Add this line
    
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(\App\Models\Permission::class, 'role_permissions', 'role_id', 'permission_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginSession extends Model
{
    use HasFactory;

    public $timestamps = false; // Add this line to disable timestamps

    protected $fillable = [
        'user_id',
        'login_time',
        'logout_time',
        'ip_address'
    ];

    protected $casts = [
        'login_time' => 'string', // Keep as string to handle null values properly,
        'logout_time' => 'string', // Keep as string to handle null values properly,
        'ip_address' => 'string', // Keep as string to handle null values properly,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /*funtion to return data to adminlog*/
    public function adminLogs()
    {
        return $this->hasMany(AdminLog::class, 'session_id');
    }
}
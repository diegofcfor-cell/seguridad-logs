<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'identifier', 'ip_address', 'status',
        'user_agent', 'created_at',
    ];
}
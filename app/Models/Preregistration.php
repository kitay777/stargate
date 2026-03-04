<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/Preregistration.php
class Preregistration extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'nickname',
        'email',
        'ip',
        'user_agent',
        'created_at',
    ];
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', // 🔹 追加する
        'firstname',
        'lastname',
        'phone',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'sex',
        'birthday',
        'title',
        'message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


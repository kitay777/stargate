<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AgencyUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'agency_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function role()
    {
        return $this->hasOne(AgencyUserRole::class);
    }
}

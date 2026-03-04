<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AgencyUserRole extends Model
{
    use HasFactory;

    protected $fillable = ['agency_user_id', 'role'];

    public function agencyUser()
    {
        return $this->belongsTo(AgencyUser::class);
    }
}

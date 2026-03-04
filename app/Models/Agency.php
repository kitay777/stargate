<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'contact_email',
        'notes',
    ];

    // 🔹 所属ライバー
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // 🔹 所属管理者（ログイン可能なagency_users）
    public function agencyUsers()
    {
        return $this->hasMany(AgencyUser::class);
    }
}

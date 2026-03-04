<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAvatarClothes extends Model
{
    protected $table = 'user_avatar_clothes';

    protected $fillable = [
        'user_id',
        'avatar_id',
        'clothes_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function avatar()
    {
        return $this->belongsTo(Avatar::class);
    }

    public function clothes()
    {
        return $this->belongsTo(Clothes::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvatarApngPart extends Model
{
    protected $fillable = [
        'avatar_id',
        'base_path',
        'eyes_open_path',
        'eyes_close_path',
        'mouth_open_path',
        'mouth_close_path',
    ];
    public function avatar()
    {
        return $this->belongsTo(Avatar::class);
    }
}

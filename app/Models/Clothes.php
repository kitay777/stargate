<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clothes extends Model
{
    protected $fillable = [
        'avatar_id',
        'name',
        'file_path',
        'thumbnail',
        'price',
        'sort_order',
        'is_active',
        'is_visible',
    ];


    public function avatar()
    {
        return $this->belongsTo(Avatar::class);
    }

    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'user_avatar_clothes'
        )->withPivot('avatar_id')
            ->withTimestamps();
    }
}

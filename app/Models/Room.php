<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Inertia\Inertia;

class Room extends Model
{
    //
    protected $fillable = [
        'user_id',
        'room_id',
        'name',
        'description',
        'start',
        'end',
        'category_id',
        'image_path',
        'tips_count',
        'total_like',
        'is_personal',
    ];    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tips()
    {
        return $this->hasMany(Tip::class);
    }
    public function likedBy()
    {
        return $this->belongsToMany(User::class, 'room_likes')->withTimestamps();
    }    
    public function isLikedBy(User $user): bool
    {
        return $this->likedBy->contains($user);
    }
    

}

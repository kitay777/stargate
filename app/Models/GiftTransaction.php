<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiftTransaction extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'room_id',
        'amount',
        'tip_image_id',
        'price',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function tipImage()
    {
        return $this->belongsTo(TipImage::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    //
    protected $fillable = [
        'product_id',
        'room_id',
        'user_id',
        'auction_bid_id',
        'quantity',
        'price',
        'shipping_price',
        'fee',
        'status',
    ];

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }


}

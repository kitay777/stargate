<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'auction_bid_id',
        'price',
        'shipping_fee',
        'fee',
        'quantity',
        'status',
    ];
    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }
}

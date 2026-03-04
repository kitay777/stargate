<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Product extends Model
{
    //
    protected $fillable = [
        'user_id', // オーナー
        'seller_id', // 販売者
        'name',
        'description',
        'price',
        'stock',
        'auction_type',
        'min_price',
        'start_at',
        'end_at',
        'category_id',
        'shipping_type',
        'shipping_fee',
        'room_id',
    ];
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function room()
    {
        return $this->belongsTo(Room::class);
    }
    public function sales()
    {
        return $this->hasMany(\App\Models\Sale::class);
    }
    // ✅ 商品オーナー（登録者）
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ✅ 販売担当者（ライバー）
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
    public function sellers()
    {
        return $this->belongsToMany(User::class, 'product_sellers')
                    ->withPivot('price')
                    ->withTimestamps();
    }

    // app/Models/Product.php
    public function scopeAuctions($q) {
        return $q->whereIn('auction_type', ['auction', 'reverse']);
    }
    public function scopeOpenAt($q, \DateTimeInterface $now) {
        return $q->where('start_at', '<=', $now)->where('end_at', '>=', $now);
    }

    

}

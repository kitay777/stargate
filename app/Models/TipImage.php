<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipImage extends Model
{
    protected $fillable = [
        'name',
        'image_path',
        'thumbnail_path',
        'price',
        'sort',
        'is_active'
    ];
}

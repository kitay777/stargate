<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\TipImage;


class PointTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'description',
        'related_user_id',
        'room_id',
        'tip_image_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // PointTransaction.php

    public function tipImage()
    {
        return $this->belongsTo(TipImage::class);
    }
}

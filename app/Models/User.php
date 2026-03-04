<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Profile;
use App\Models\RoomMessage;
use App\Models\ChatMessage;
use App\Models\Iine;
use App\Models\Okiniiri;
use App\Models\Room;
use App\Models\Agency;
use App\Models\Product;
use App\Models\Avatar;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use SoftDeletes;
    public const TYPE_NORMAL = 'normal';
    public const TYPE_SELLER = 'seller';
    public const TYPE_ADMIN = 'admin';


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'agency_id',
        'profile_photo_path',
        'avatar_id',
        'type',
        'is_banned',
        'ban_reason',
        'ban_until',
        'line_user_id',
        'is_line_friend',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function broadcastOn($event)
    {
        return ['id' => $this->id, 'name' => $this->name];
    }
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
    public function roomMessages()
    {
        return $this->hasMany(RoomMessage::class);
    }
    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class);
    }
    public function iines()
    {
        return $this->hasMany(Iine::class);
    }
    public function okiniiris()
    {
        return $this->hasMany(Okiniiri::class);
    }
    public function likedRooms()
    {
        return $this->belongsToMany(Room::class, 'room_likes')->withTimestamps();
    }

    // User.php
    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function isSeller()
    {
        return $this->type === self::TYPE_SELLER;
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function sellingProducts()
    {
        return $this->belongsToMany(Product::class, 'product_sellers');
    }

    public function avatar()
    {
        return $this->belongsTo(Avatar::class);
    }
    
    public function avatars()
    {
        return $this->hasMany(Avatar::class);
    }
    // app/Models/User.php

    public function avatarClothes()
    {
        return $this->hasMany(UserAvatarClothes::class);
    }

    public function clothes()
    {
        return $this->belongsToMany(
            Clothes::class,
            'user_avatar_clothes'
        )->withPivot('avatar_id')
            ->withTimestamps();
    }
    public function isAdmin()
    {
        return $this->type === 'admin';
    }
}

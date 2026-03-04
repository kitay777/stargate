<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Avatar extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'vrm_path',
        'thumbnail_path',
        'width',
        'height',
        'is_active',
        'role',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'url',               // VRM 用
        'thumbnail_url',
        'apng_parts_urls',   // ★ APNG 用
    ];

    /**
     * VRM の URL
     */
    public function getUrlAttribute(): ?string
    {
        if ($this->type === 'vrm' && $this->vrm_path) {
            return Storage::url($this->vrm_path);
        }

        return null;
    }

    /**
     * APNG（2Dアバター）用 URL セット
     */
    public function getApngPartsUrlsAttribute(): ?array
    {
        if ($this->type !== 'apng' || !$this->apngParts) {
            return null;
        }

        return [
            'base'        => Storage::url($this->apngParts->base_path),
            'eyes_open'  => Storage::url($this->apngParts->eyes_open_path),
            'eyes_close' => Storage::url($this->apngParts->eyes_close_path),
            'mouth_open' => Storage::url($this->apngParts->mouth_open_path),
            'mouth_close' => Storage::url($this->apngParts->mouth_close_path),
        ];
    }

    /**
     * サムネイルURL（VRM用）
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_path
            ? Storage::url($this->thumbnail_path)
            : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function apngParts()
    {
        return $this->hasOne(AvatarApngPart::class);
    }

    public function clothes()
    {
        return $this->hasMany(Clothes::class);
    }

    public function userSelections()
    {
        return $this->hasMany(UserAvatarClothes::class);
    }
}

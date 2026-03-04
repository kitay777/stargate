<?php

namespace App\Events;

use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RoomGiftSent implements ShouldBroadcast
{
    public $user;
    public $roomId;
    public $gift;

    public function __construct($user, $room, $gift)
    {
        $this->user = $user;
        $this->roomId = $room->id;
        $this->gift = $gift;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('roomchat.' . $this->roomId);
    }

    public function broadcastWith()
    {
        return [
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'profile_photo_url' => $this->user->profile_photo_url,
            ],
            'gift' => [
                'id' => $this->gift->id,
                'name' => $this->gift->name,
                'image_path' => $this->gift->image_path,
                'price' => $this->gift->price,
            ],
        ];
    }
}

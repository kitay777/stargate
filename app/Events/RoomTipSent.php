<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// app/Events/RoomTipSent.php
class RoomTipSent implements ShouldBroadcast
{
    public $user;
    public $amount;
    public $room;

    public function __construct($user, $amount, $room)
    {
        $this->user = $user;
        $this->amount = $amount;
        $this->room = $room;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('roomchat.' . $this->room->id ?? null);
    }


    public function broadcastWith()
    {
        return [
            'user' => [
                'name' => $this->user->name,
                'profile_photo_url' => $this->user->profile_photo_url,
            ],
            'amount' => $this->amount,
        ];
    }
}

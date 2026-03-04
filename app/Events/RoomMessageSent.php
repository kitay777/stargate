<?php

namespace App\Events;

use App\Models\RoomMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class RoomMessageSent implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(RoomMessage $message)
    {
        // メッセージと一緒に user を事前に読み込む（これが重要）
        $this->message = $message->load('user');
    }

    public function broadcastOn(): array
    {
        \Log::info("✅ broadcastOn called: room_id={$this->message->room_id}");

        return [
            new PresenceChannel("roomchat.{$this->message->room_id}"),
        ];
    }

    /**
     * ブロードキャストで送る内容を明示的に定義
     */
    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'message' => $this->message->message,
                'user_id' => $this->message->user_id,
                'created_at' => $this->message->created_at->toISOString(),

                // 👇 user を含める！
                'user' => [
                    'id' => $this->message->user->id,
                    'name' => $this->message->user->name,
                    'profile_photo_url' => $this->message->user->profile_photo_url,
                ]
            ]
        ];
    }
}

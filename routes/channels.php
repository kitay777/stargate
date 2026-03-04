<?php

use Illuminate\Support\Facades\Broadcast;

use Illuminate\Support\Facades\Log;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('chat.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('roomchat.{roomId}', function ($user, $roomId) {
    \Log::info("✅ roomchat channel accessed by user ID: {$user->id}, room ID: {$roomId}");
    // ルームに参加しているユーザーの情報を取得
    return [
        'roomId' => $roomId,
        'userId' => $user->id,
        'userName' => $user->name ?? "User{$user->id}",
        'id' => $user->id,
        'name' => $user->name ?? "User{$user->id}",
    ];
});
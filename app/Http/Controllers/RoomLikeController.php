<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;

class RoomLikeController extends Controller
{
    //
    public function toggle(Request $request, Room $room)
    {
        $user = $request->user();

        if ($room->likedBy()->where('user_id', $user->id)->exists()) {
            // 👎 いいね済 → いいね解除
            $room->likedBy()->detach($user->id);
        } else {
            // ❤️ いいねしてない → いいね追加
            $room->likedBy()->attach($user->id);
        }

        return back(); // Inertia使ってれば router.post() のままでOK
    }

    public function likedPresenters()
    {
        $user = auth()->user();

        // 自分がLIKEしたルームを取得（配信者情報も一緒に）
        $rooms = $user->likedRooms()->with('user')->get();

        // 配信者を抽出し、ユニーク化（重複なし）
        $presenters = $rooms
            ->pluck('user')
            ->unique('id')
            ->values(); // ← 0から順に詰め直し

        return inertia('MyLikes/Presenters', [
            'presenters' => $presenters,
        ]);
    }

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\RoomTipSent;
use App\Models\Room;

class RoomTipController extends Controller
{
    //// app/Http/Controllers/RoomTipController.php


    public function store(Request $request, Room $room)
    {
        $validated = $request->validate([
            'amount' => 'required|integer|min:100',
        ]);

        // 任意で DB に保存（例: RoomTip モデル）
        // RoomTip::create([...]);

        // イベントをブロードキャスト
        broadcast(new RoomTipSent(auth()->user(), $room, $validated['amount']))->toOthers();

        return response()->json(['status' => 'success']);
    }

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tip;
use App\Models\Room;                      // ← 追加
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;        // ← 追加
use App\Services\PointService;            // ← 追加
use App\Events\RoomTipSent;               // ← 追加

class TipController extends Controller
{
    public function tip(Request $request, Room $room)
    {
        log::info('TIP REQUEST', [
            'userId' => $request->user()->id,
            'roomId' => $room->id,
            'amount' => $request->amount,
        ]);
        $request->validate([
            'amount' => 'required|integer|min:100'
        ]);

        $sender = auth()->user();
        $amount = $request->amount;

        if ($sender->points < $amount) {
            return response()->json([
                'error' => 'ポイントが不足しています'
            ], 400);
        }

        $receiver = $room->user;

        if ($sender->id === $receiver->id) {
            return response()->json([
                'error' => '自分には投げ銭できません'
            ], 400);
        }

        try {

            $pointService = app(PointService::class);

            DB::transaction(function () use ($sender, $receiver, $amount, $room, $pointService) {

                $pointService->deduct($sender, $amount, 'tip_send', [
                    'related_user_id' => $receiver->id,
                    'room_id' => $room->id,
                ]);

                $pointService->add($receiver, $amount, 'tip_receive', [
                    'related_user_id' => $sender->id,
                    'room_id' => $room->id,
                ]);
            });

            broadcast(new RoomTipSent($sender, $amount, $room))->toOthers();


            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {

            \Log::error('TIP ERROR', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}

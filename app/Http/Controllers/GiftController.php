<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\TipImage;
use App\Models\GiftTransaction;
use Illuminate\Support\Facades\DB;
use App\Services\PointService;
use App\Events\RoomGiftSent;
use App\Models\Gift;
use Illuminate\Support\Facades\Log;

class GiftController extends Controller
{
    public function send(Request $request, Room $room)
    {
        Log::info('GiftController@send called', ['room_id' => $room->id, 'user_id' => auth()->id()]);
        $request->validate([
            'gift_id' => 'required|exists:tip_images,id',
        ]);

        Log::info('GiftController@send validation passed', ['gift_id' => $request->gift_id]);
        $sender = auth()->user();
        Log::info('GiftController@send sender identified', ['sender_id' => $sender->id]);
        $gift = TipImage::findOrFail($request->gift_id);
        Log::info('GiftController@send gift identified', ['gift_id' => $gift->id, 'gift_price' => $gift->price]);
        $receiver = $room->user;
        Log::info('GiftController@send sender and receiver identified', ['sender_id' => $sender->id, 'receiver_id' => $receiver->id, 'gift_price' => $gift->price]);
        if ($sender->id === $receiver->id) {
            return response()->json([
                'error' => '自分には送れません'
            ], 400);
        }
        Log::info('GiftController@send===== sender and receiver identified', ['sender_id' => $sender->id, 'receiver_id' => $receiver->id, 'gift_price' => $gift->price]);
        try {

            DB::transaction(function () use ($sender, $receiver, $gift, $room) {

                $pointService = app(PointService::class);

                // ポイント減算
                $pointService->deduct(
                    $sender,
                    $gift->price,
                    'gift_send',
                    [
                        'related_user_id' => $receiver->id,
                        'room_id' => $room->id,
                        'gift_id' => $gift->id,
                    ]
                );

                // ポイント加算
                $pointService->add(
                    $receiver,
                    $gift->price,
                    'gift_receive',
                    [
                        'related_user_id' => $sender->id,
                        'room_id' => $room->id,
                        'gift_id' => $gift->id,
                    ]
                );

                GiftTransaction::create([
                    'sender_id' => $sender->id,
                    'receiver_id' => $receiver->id,
                    'room_id' => $room->id,
                    'amount' => $gift->price,
                    'tip_image_id' => $gift->id,
                    'price' => $gift->price,
                ]);
            });

            broadcast(new RoomGiftSent($sender, $room, $gift))->toOthers();

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {

            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}

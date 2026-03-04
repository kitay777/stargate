<?php

namespace App\Http\Controllers;

use App\Models\RoomMessage;
use App\Models\Room; 
use Illuminate\Http\Request;
use App\Events\RoomMessageSent; 

use Illuminate\Support\Facades\Log;

class RoomMessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Room $room)
    {
        //
        $message = RoomMessage::create([
            'user_id' => auth()->id(),
            'room_id' => $room->id,
            'message' => $request->input('message'),
            'type' => "0",
        ]);

        Log::info('📤 RoomMessage を作成しました: ' . $message->id);

        broadcast(new RoomMessageSent($message))->toOthers();
    
        Log::info('📣 broadcast を呼び出しました！');

        return response()->json($message);
    }

    /**
     * Display the specified resource.
     */
    public function show($room)
    {
        //
        /*$messages = RoomMessage::where('room_id', $room)
            ->orderBy('created_at', 'asc')
            ->get();*/
        $messages = RoomMessage::with('user')->where('room_id', $room)->get();

        return response()->json($messages);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RoomMessage $room_message)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RoomMessage $room_message)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RoomMessage $room_message)
    {
        //
    }
}

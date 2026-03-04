<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Inertia\Inertia;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use App\Models\Room;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;


class RoomController extends Controller
{
    use AuthorizesRequests;
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1024',
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp',
        ]);
    
        $imagePath = null;
    
        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
        
            // ✅ ImageManager v3 初期化 (GDドライバ使用)
            $manager = ImageManager::withDriver(new GdDriver());
        
            // ✅ リサイズ＆トリミング
            $image = $manager->read($imageFile)->cover(1240, 744);
        
            $filename = uniqid('room_') . '.' . $imageFile->getClientOriginalExtension();
        
            // ✅ 保存（JPEG品質90）
            Storage::disk('public')->put("rooms/{$filename}", (string) $image->toJpeg(90));
        
            $imagePath = "rooms/{$filename}";
        }
    
        $room = Room::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'description' => $validated['description'],
            'start' => $validated['start'],
            'end' => $validated['end'],
            'category_id' => $validated['category_id'],
            'image_path' => $imagePath,
        ]);
    
        return redirect()->route('dashboard')->with('success', 'ルームを作成しました！');
    }

    public function apistore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1024',
            'start' => 'nullable|date',
            'end' => 'nullable|date|after_or_equal:start',
            'category_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp',
        ]);
    
        $imagePath = null;
    
        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
        
            // ✅ ImageManager v3 初期化 (GDドライバ使用)
            $manager = ImageManager::withDriver(new GdDriver());
        
            // ✅ リサイズ＆トリミング
            $image = $manager->read($imageFile)->cover(1240, 744);
        
            $filename = uniqid('room_') . '.' . $imageFile->getClientOriginalExtension();
        
            // ✅ 保存（JPEG品質90）
            Storage::disk('public')->put("rooms/{$filename}", (string) $image->toJpeg(90));
        
            $imagePath = "rooms/{$filename}";
        }
        $start = $validated['start'] ?? now();
        $end = $validated['end'] ?? now()->addMinutes(30);
        $room = Room::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'description' => $validated['description'],
            'start' => $start,
            'end' => $end,
            'category_id' => $validated['category_id'],
            'image_path' => $imagePath,
        ]);
    
        return response()->json([
            'id' => $room->id,
            'name' => $room->name,
            'description' => $room->description,
            'category_id' => $room->category_id,
        ]);


    }


    /**
     * Display the specified resource.
     */
    public function show(Room $room)
    {
        $room->load(['user', 'category'])
             ->loadCount('likedBy')
             ->load(['likedBy' => fn($q) => $q->where('user_id', auth()->id())]);
    
        // streaming を追加
        $room->setAttribute('streaming', $this->isRoomStreaming($room->id));
    
        // 🔥 配列にして明示的に渡す！
        $roomArray = $room->toArray();
        $roomArray['streaming'] = $room->streaming;
    
        return Inertia::render('Rooms/Show', [
            'scheduledRooms' => $room,
            'room' => $roomArray,
        ]);
    }
    

    public function isRoomStreaming(int $id)
    {
        $room = Room::find($id);
        try {
            $response = Http::get('https://moon.timesfun.net:8443/status');
    
            if ($response->successful()) {
                $streamingRooms = $response->json(); // [{ room: 1, viewers: 3 }, ...]

                // 対象の room.id が配信中に含まれているかチェック
                foreach ($streamingRooms as $streamingRoom) {
                    
                    if ((string) $streamingRoom['room'] === (string) $id) {
                        \Log::info("📡 Room {$id} is streaming: ", $streamingRoom);
                        if($streamingRoom['streaming'] == 1) {
                            return $streamingRoom;
                        } else {
                            //return $streamingRoom;
                            return false;
                        }
                    }
                }
            }
    
        } catch (\Exception $e) {
            \Log::error('🚨 Kurentoステータス取得失敗: ' . $e->getMessage());
            return false;
        }
    
        return false;
    }
    
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Room $room)
    {
        //$this->authorize('update', $room); // optional: 認可
    
        $categories = \App\Models\Category::all();
        Log::info('Categories:', $categories->toArray());
        return Inertia::render('Rooms/Edit', [
            'room' => $room->load('category'),
            'categories' => $categories,
        ]);
    }
    

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Room $room)
    {
        //$this->authorize('update', $room); // optional: 自分のルームだけ更新可能に
    
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1024',
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp',
        ]);
    
        if ($request->hasFile('image')) {
            // 保存処理はstore()と同じ
            $imageFile = $request->file('image');
            $manager = ImageManager::withDriver(new GdDriver());
            $image = $manager->read($imageFile)->cover(1240, 744);
            $filename = uniqid('room_') . '.' . $imageFile->getClientOriginalExtension();
            Storage::disk('public')->put("rooms/{$filename}", (string) $image->toJpeg(90));
            $room->image_path = "rooms/{$filename}";
        }
    
    
        $room->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'start' => $validated['start'],
            'end' => $validated['end'],
            'category_id' => $validated['category_id'],
        ]);
    
        return redirect()->route('dashboard')->with('success', 'ルームを更新しました！');
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
        //
    }
}

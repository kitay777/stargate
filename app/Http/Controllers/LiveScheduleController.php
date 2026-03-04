<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use Inertia\Inertia;
use Carbon\Carbon;

class LiveScheduleController extends Controller
{
    public function index()
    {
        $sales = Sale::with(['product', 'room.user','product.category', ])
            ->join('rooms', 'sales.room_id', '=', 'rooms.id')
            ->where('rooms.start', '>=', now())
            ->orderBy('rooms.start')
            ->select('sales.*') // ← joinするときは select 明示が必要
            ->get();
    
            return Inertia::render('LiveSchedule/ListView', [
                'schedules' => $sales->map(function ($sale) {
                    return [
                        'id' => $sale->id,
                        'start' => $sale->room->start,
                        'user_name' => $sale->room->user->name,
                        'user_photo' => $sale->room->user->profile_photo_path
                            ? asset('storage/' . $sale->room->user->profile_photo_path)
                            : '/images/default-profile.png',
                        'product_name' => $sale->product->name,
                        'product_image' => $sale->product->images[0]->path ?? '/images/default-product.png',
                        'category_name' => $sale->product->category->name ?? '未分類',
                        'room_id' => $sale->room->id,
                    ];
                }),
            ]);
            
            
    }
    public function calendar()
    {
        $sales = Sale::with(['product', 'room.user'])
            ->whereHas('room', fn($q) => $q->where('start', '>=', now()))
            ->get();


        $events = $sales->map(function ($sale) {
            return [
                'title' => $sale->product->name . '（' . $sale->room->user->name . '）',
                'start' => Carbon::parse($sale->room->start)->toIso8601String(),
                'end'   => Carbon::parse($sale->room->end)->toIso8601String(),
                'url'   => '/viewer/' . $sale->room->id,
            ];
        });
            

        return Inertia::render('LiveSchedule/CalendarView', [
            'events' => $events,
        ]);
    }

}

<?php

namespace App\Http\Controllers;
use App\Models\Tip;
use App\Models\Room;
use Illuminate\Http\Request;

use Auth;
class MyTipController extends Controller
{
    public function index()
    {
        $tips = Tip::with(['room', 'user'])
            ->whereHas('room', fn($q) => $q->where('user_id', auth()->id()))
            ->latest()
            ->get();

        $total = $tips->sum('amount'); // ✅ 合計金額

        return inertia('MyTips/Index', [
            'tips' => $tips,
            'total' => $total, // ← これもVueに渡す
        ]);
    }
}
    
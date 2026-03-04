<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Iine;
use App\Models\Okiniiri;
use Inertia\Inertia;
use App\Models\Room;

class UserController extends Controller
{
    public function getUserCounts($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $iine_count = Iine::where('target_id', $id)->count();
        $okiniiri_count = Okiniiri::where('target_id', $id)->count();
        $liked = Iine::where('user_id', auth()->id())->where('target_id', $id)->exists();
        $favorited = Okiniiri::where('user_id', auth()->id())->where('target_id', $id)->exists();

        return response()->json([
            'iine_count' => $iine_count,
            'okiniiri_count' => $okiniiri_count,
            'liked' => $liked,
            'favorited' => $favorited
        ]);
    }
    public function show(User $user)
    {
        $userId = $user->id;
        $user->load('profile');


        // 今後の予約ルームだけ取得（開始日時順）
        $rooms = $user->rooms()
            ->with(['category', 'user', 'likedBy'])
            ->where('start', '>=', now())
            ->orderBy('start')
            ->get()
            ->map(function ($room) use ($userId) {
                $room->liked_by = $room->likedBy->where('id', $userId)->values();
                $room->liked_by_count = $room->likedBy->count();
                return $room;
            });


        return Inertia::render('Users/Show', [
            'user' => $user,
            'reservedRooms' => $rooms,
        ]);
    }
    public function adminIndex(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        return Inertia::render('Admin/Users', [
            'users' => $query->latest()->paginate(20)->withQueryString(),
            'filters' => $request->only(['search', 'type'])
        ]);
    }
    public function ban(User $user)
    {
        $user->update([
            'is_banned' => true,
            'ban_reason' => request('reason'),
            'ban_until' => request('until'),
        ]);

        return back();
    }

    public function unban(User $user)
    {
        $user->update([
            'is_banned' => false,
            'ban_reason' => null,
            'ban_until' => null,
        ]);

        return back();
    }
}

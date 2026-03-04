<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Avatar;
use App\Models\Clothes;
use App\Models\UserAvatarClothes;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AvatarController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $officialAvatars = Avatar::where('role', 'official')
            ->where('is_active', 1)
            ->orderBy('sort')
            ->get();

        $userAvatars = Avatar::where('user_id', $user->id)
            ->where('is_active', 1)
            ->orderByDesc('created_at')
            ->get();

        return inertia('Avatar/Select', [
            'officialAvatars' => $officialAvatars,
            'userAvatars'     => $userAvatars,
            'currentAvatarId' => $user->avatar_id,
        ]);
    }

    public function select(Avatar $avatar)
    {
        $user = auth()->user();

        // 🛑 セキュリティチェック
        if ($avatar->role !== 'official' && $avatar->user_id !== $user->id) {
            abort(403);
        }

        $user->update([
            'avatar_id' => $avatar->id
        ]);

        return back()->with('success', 'アバターを変更しました');
    }


    public function clothes(Avatar $avatar)
    {
        return Inertia::render('Avatar/ClothesSelect', [
            'avatar' => $avatar
        ]);
    }
}

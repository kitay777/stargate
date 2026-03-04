<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Avatar;
use App\Models\Clothes;
use App\Models\UserAvatarClothes;
use Illuminate\Support\Facades\Auth;

class AvatarClothesController extends Controller
{
    /**
     * そのアバターに紐づく服一覧
     */
    public function index(Avatar $avatar)
    {
        $clothes = $avatar->clothes()
            ->where('is_active', true)
            ->where('is_visible', true)        // ← 追加
            ->orderBy('sort_order')            // ← 追加
            ->get();

        return response()->json($clothes);
    }

    /**
     * 服選択保存
     */
    public function select(Request $request, Avatar $avatar)
    {
        $request->validate([
            'clothes_id' => 'required|exists:clothes,id'
        ]);

        $user = Auth::user();

        $clothes = Clothes::where('id', $request->clothes_id)
            ->where('avatar_id', $avatar->id)
            ->where('is_active', true)
            ->where('is_visible', true)
            ->firstOrFail();

        // 🔥 将来的に priceチェックをここに入れられる

        UserAvatarClothes::updateOrCreate(
            [
                'user_id'   => $user->id,
                'avatar_id' => $avatar->id,
            ],
            [
                'clothes_id' => $clothes->id
            ]
        );

        return response()->json([
            'status' => 'ok'
        ]);
    }

    /**
     * 現在選択中の服
     */
    public function current(Avatar $avatar)
    {
        $user = Auth::user();

        $selected = UserAvatarClothes::where('user_id', $user->id)
            ->where('avatar_id', $avatar->id)
            ->with('clothes')
            ->first();

        // 非公開・停止服だった場合の安全対策
        if ($selected && (!$selected->clothes->is_active || !$selected->clothes->is_visible)) {
            return response()->json(null);
        }

        return response()->json($selected?->clothes);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Avatar;
use Illuminate\Http\Request;

class AvatarController extends Controller
{
    public function index()
    {
        return Avatar::where('is_active', true)
            ->orderBy('id')
            ->get();
    }

    public function select(Request $request)
    {
        $request->validate([
            'avatar_id' => ['required', 'exists:avatars,id'],
        ]);

        $avatar = Avatar::where('id', $request->avatar_id)
            ->where('is_active', true)
            ->firstOrFail();

        $user = $request->user();
        $user->avatar_id = $avatar->id;
        $user->save();

        return response()->json([
            'success' => true,
            'avatar' => $avatar,
        ]);
    }
}

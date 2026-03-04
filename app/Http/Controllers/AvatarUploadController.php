<?php

namespace App\Http\Controllers;

use App\Models\Avatar;
use App\Models\AvatarApngPart;
use Illuminate\Http\Request;

class AvatarUploadController extends Controller
{
    public function create()
    {
        return inertia('Avatar/Upload');
    }

    public function store(Request $request)
    {
        $user = $request->user();
        abort_if(!$user, 401);


        $request->validate([
            'type' => 'required|in:vrm,apng',
            'name' => 'nullable|string|max:255',

            // VRM
            'vrm' => 'nullable|required_if:type,vrm|file|max:102400', // 100MB
            'thumbnail' => 'nullable|image|max:2048',

            // APNG parts
            'base'        => 'nullable|required_if:type,apng|image|mimes:png',
            'eyes_open'  => 'nullable|required_if:type,apng|image|mimes:png',
            'eyes_close' => 'nullable|required_if:type,apng|image|mimes:png',
            'mouth_open' => 'nullable|required_if:type,apng|image|mimes:png',
            'mouth_close' => 'nullable|required_if:type,apng|image|mimes:png',
        ]);

        // Avatar 本体
        $avatar = Avatar::create([
            'user_id' => $user->id,
            'name' => $request->name ?? strtoupper($request->type) . ' Avatar',
            'type' => $request->type,
            'is_active' => true,
        ]);

        // VRM
        if ($request->type === 'vrm') {
            $avatar->update([
                'vrm_path' => $request->file('vrm')->store('avatars/vrm', 'public'),
                'thumbnail_path' => $request->file('thumbnail')
                    ? $request->file('thumbnail')->store('avatars/thumbnails', 'public')
                    : null,
            ]);
        }

        // APNG
        if ($request->type === 'apng') {
            $dir = "avatars/apng/{$avatar->id}";

            AvatarApngPart::create([
                'avatar_id' => $avatar->id,
                'base_path' => $request->file('base')->store($dir, 'public'),
                'eyes_open_path' => $request->file('eyes_open')->store($dir, 'public'),
                'eyes_close_path' => $request->file('eyes_close')->store($dir, 'public'),
                'mouth_open_path' => $request->file('mouth_open')->store($dir, 'public'),
                'mouth_close_path' => $request->file('mouth_close')->store($dir, 'public'),
            ]);
        }

        $user->update([
            'avatar_id' => $avatar->id,
        ]);

        return redirect()->route('avatar.select')
            ->with('success', 'アバターを登録しました');
    }
}

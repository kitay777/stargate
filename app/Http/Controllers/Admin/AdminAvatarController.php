<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Avatar;
use Illuminate\Http\Request;

class AdminAvatarController extends Controller
{
    public function create()
    {
        return inertia('Admin/Avatars/Create');
    }

    public function index()
    {
        $avatars = Avatar::where('role', 'official')
            ->orderBy('sort')
            ->get();

        return inertia('Admin/Avatars/Index', [
            'avatars' => $avatars
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:vrm,apng',
            'file' => 'required|file',
            'thumbnail' => 'nullable|image',
            'sort' => 'nullable|integer',
        ]);

        $path = $request->file('file')->store('avatars/official', 'public');

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('avatars/thumbs', 'public');
        }

        Avatar::create([
            'user_id'        => null,
            'name'           => $request->name,
            'type'           => $request->type,
            'role'           => 'official',
            'vrm_path'       => $request->type === 'vrm' ? "$path" : null,
            'apng_path'      => $request->type === 'apng' ? "$path" : null,
            'thumbnail_path' => $thumbnailPath ? "$thumbnailPath" : null,
            'sort'           => $request->sort ?? 0,
            'is_active'      => 1,
        ]);

        return redirect()->route('admin.avatars.index')
            ->with('success', '公式アバター登録完了');
    }
    public function destroy(Avatar $avatar)
    {
        // 公式だけ削除可にする（安全）
        if ($avatar->role !== 'official') {
            abort(403);
        }

        $avatar->delete();

        return redirect()->route('admin.avatars.index')
            ->with('success', '削除しました');
    }
}

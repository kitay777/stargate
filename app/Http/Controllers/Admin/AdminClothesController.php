<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Clothes;
use App\Models\Avatar;
use Illuminate\Support\Facades\Storage;

class AdminClothesController extends Controller
{
    /**
     * 一覧表示
     */
    public function index()
    {
        $clothes = Clothes::with('avatar')
            ->orderBy('sort_order')
            ->get();

        $avatars = Avatar::all();

        return inertia('Admin/Clothes/Index', [
            'clothes' => $clothes,
            'avatars' => $avatars,
        ]);
    }

    /**
     * 新規作成画面
     */
    public function create()
    {
        $avatars = Avatar::all();

        return inertia('Admin/Clothes/Create', [
            'avatars' => $avatars,
        ]);
    }

    /**
     * 保存
     */
    public function store(Request $request)
    {
        $request->validate([
            'avatar_id'  => 'required|exists:avatars,id',
            'name'       => 'required|string|max:255',
            'file'       => 'required|file',
            'thumbnail'  => 'required|image',
            'price'      => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $filePath = $request->file('file')
            ->store('avatars/vrm', 'public');

        $thumbPath = $request->file('thumbnail')
            ->store('avatars/vrm/thumbs', 'public');

        Clothes::create([
            'avatar_id'  => $request->avatar_id,
            'name'       => $request->name,
            'file_path'  => $filePath,
            'thumbnail'  => $thumbPath,
            'price'      => $request->price ?? 0,
            'sort_order' => $request->sort_order ?? 0,
            'is_active'  => true,
            'is_visible' => $request->boolean('is_visible'),
        ]);

        return redirect('/admin/clothes')
            ->with('success', '服を登録しました');
    }

    /**
     * 削除
     */
    public function destroy(Clothes $clothes)
    {
        // ファイル削除
        if ($clothes->file_path) {
            Storage::disk('public')->delete($clothes->file_path);
        }

        if ($clothes->thumbnail) {
            Storage::disk('public')->delete($clothes->thumbnail);
        }

        $clothes->delete();

        return back()->with('success', '削除しました');
    }

    /**
     * 有効/停止 切替
     */
    public function toggleActive(Clothes $clothes)
    {
        $clothes->update([
            'is_active' => !$clothes->is_active
        ]);

        return back();
    }

    /**
     * 公開/非公開 切替
     */
    public function toggleVisible(Clothes $clothes)
    {
        $clothes->update([
            'is_visible' => !$clothes->is_visible
        ]);

        return back();
    }

    /**
     * 並び順更新（必要なら）
     */
    public function updateSort(Request $request, Clothes $clothes)
    {
        $request->validate([
            'sort_order' => 'required|integer|min:0'
        ]);

        $clothes->update([
            'sort_order' => $request->sort_order
        ]);

        return back();
    }
}

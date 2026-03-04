<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\ProductImage;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProductImageController extends Controller
{
    public function store(Request $request, Product $product)
    {
        Log::info('✅ ProductImageController@index に入りました');
        // アクセス制限（ログイン中のユーザーが出品者か確認）
        if ($product->user_id !== auth()->id()) {
            abort(403, '許可されていない操作です。');
        }

        $request->validate([
            'images.*' => 'required|image|max:1024000', // 複数画像対応
        ]);
        
        Log::info('📸 画像:', ['count' => count($request->file('images', []))]);

        $manager = new ImageManager(new Driver());
        foreach ($request->file('images', []) as $file) {
            $img = $manager->read($file->getRealPath()); // ✅ Manager経由でreadする
    
            $img->scale(width: 1280, height: 1280);
    
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = 'products/' . $filename;
    
            Storage::disk('public')->put($path, (string) $img->toJpeg(80));
    
            ProductImage::create([
                'product_id' => $product->id,
                'path' => Storage::url($path),
            ]);
        }

        return back()->with('ok', '画像をアップロードしました。');
    }

    public function destroy($id)
    {
        $image = ProductImage::findOrFail($id);
        Log::info('✅ 削除処理に入りました');
        // セキュリティチェック（オーナーか確認）
        if ($image->product->user_id !== auth()->id()) {
            abort(403, '許可されていない操作です。');
        }

        // ファイルを削除
//        $filePath = str_replace('/storage/', 'public/', $image->path);
//        Storage::delete($filePath);

        // DBから削除
        $image->delete();

        return back()->with('success', '画像を削除しました。');
    }
}

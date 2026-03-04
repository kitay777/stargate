<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\AuctionBid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuctionPriceController extends Controller
{
    public function update(Request $request, Product $product)
    {
        $product->loadMissing('sellers');
        
        if (!$product->sellers->contains(Auth::id())) {
            abort(403, '許可されていない操作です。');
        }
    
        if (!in_array($product->auction_type, ['auction', 'reverse'])) {
            abort(400, 'オークション商品ではありません。');
        }
    
        $request->validate([
            'delta' => 'required|integer',
        ]);
    
        $delta = $request->delta;
    
        // ✅ ここ！商品共通priceを使う
        $newPrice = $product->price + $delta;
    
        if ($product->auction_type === 'reverse' && $product->min_price !== null) {
            if ($newPrice < $product->min_price) {
                $newPrice = $product->min_price;
            }
        }
    
        // ✅ 商品本体のpriceを更新
        $product->update([
            'price' => $newPrice,
        ]);
    
        // ✅ 変更履歴も通常どおり記録
        AuctionBid::create([
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'price' => $newPrice,
        ]);
    
        return back()->with('success', '金額を更新しました！');
    }
    
    
}

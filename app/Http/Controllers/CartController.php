<?php

namespace App\Http\Controllers;


use App\Models\Product;
use App\Models\AuctionBid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Sale;


class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $sales = Sale::with('product') // 商品情報も取得
            ->where('user_id', Auth::id())
            ->where('status', 'pending') // 👈 pendingだけ
            ->get();

        return Inertia::render('Cart/Index', [
            'sales' => $sales,
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'auction_bid_id' => 'nullable|exists:auction_bids,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        $price = $product->price;
        $auctionBidId = null;

        // オークション商品だったら、最新提示金額を取得
        if ($request->auction_bid_id) {
            $auctionBid = AuctionBid::findOrFail($request->auction_bid_id);
            $price = $auctionBid->price;
            $auctionBidId = $auctionBid->id;
        }

        // salesテーブルに登録
        Sale::create([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'auction_bid_id' => $auctionBidId,
            'price' => $price,
            'shipping_price' => $product->shipping_fee ?? 0,
            'fee' => 0, // 今は0、必要なら手数料ロジック追加できる
            'quantity' => $request->quantity ?? 1,
            'status' => 'pending', // カートに追加時はpending
        ]);

        return redirect()->back()->with('success', '🛒 カートに追加しました！（pending）');
    }

    /**
     * Display the specified resource.
     */
    public function show(Cart $cart)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cart $cart)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cart $cart)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cart $cart)
    {
        //
    }
    public function checkout()
    {
        Sale::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->update(['status' => 'completed']);
    
        return redirect()->route('cart.index')->with('success', '購入が完了しました！');
    }
    public function purchased()
    {
        $purchases = Sale::with('product')
        ->where('user_id', Auth::id())
        ->where('status', 'completed')
        ->orderBy('created_at', 'desc')
        ->paginate(5);

    

        return Inertia::render('Cart/Purchased', [
            'purchases' => $purchases,
        ]);
    }

    

}

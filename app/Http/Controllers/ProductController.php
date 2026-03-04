<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Category;
use App\Models\Room;
use App\Models\Sale;
use Carbon\Carbon;



class ProductController extends Controller
{
    public function publicView()
    {
        $products = Product::with(['user.profile', 'images', 'room']) // 👈 roomを追加
            ->latest()
            ->paginate(12);
    
        $streamingRooms = collect();
        try {
            $response = Http::get('https://moon.timesfun.net:8443/status');
            if ($response->successful()) {
                $streamingRooms = collect($response->json());
            }
        } catch (\Exception $e) {
            \Log::warning('⚠️ 配信ステータス取得失敗: ' . $e->getMessage());
        }
        return Inertia::render('Products/PublicIndex', [
            'products' => $products,
            'streamingRooms' => $streamingRooms,
        ]);
    }
    
    public function openAuctions(Request $request)
    {
        $perPage = (int)($request->integer('per_page') ?: 24);
        $now = Carbon::now('Asia/Tokyo');

        $products = $this->baseAuctionQuery()
            ->whereNotNull('start_at')
            ->whereNotNull('end_at')
            ->where('start_at', '<=', $now)
            ->where('end_at', '>=', $now)
            ->orderBy('end_at')        // 終了が近い順
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Auctions/Open', [
            'products'  => $products,
            'statusUrl' => config('app.streaming_status_url', 'https://moon.timesfun.net:8443/status'),
        ]);
    }

    /**
     * オークション系の共通ベースクエリ
     */
    protected function baseAuctionQuery()
    {
        return \App\Models\Product::query()
            ->whereIn('auction_type', ['auction', 'reverse'])
            ->with([
                'images:id,product_id,path',
                'user:id,name,profile_photo_path',
                'room:id,start', // 視聴リンク用
            ]);
    }





    public function index()
    {
        $products = Product::with(['images', 'category', 'room', 'sellers']) // ←ここ！！
            ->where(function ($query) {
                $query->where('user_id', auth()->id())
                    ->orWhereHas('sellers', fn($q) => $q->where('users.id', auth()->id()));
            })
            ->latest()
            ->get();

        $categories = Category::all();
        $rooms = Room::where('user_id', auth()->id())->get();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'rooms' => $rooms,
        ]);
    }


    public function store(Request $request)
    {
        Log::Info('✅ ProductController@index に入りました'.$request->start_at);
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'auction_type' => 'required|in:none,auction,reverse',
            'min_price' => 'nullable|required_if:auction_type,auction,reverse|integer|min:1',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'category_id' => 'required|exists:categories,id',  // ✅ カテゴリー
            'shipping_type' => 'required|in:included,cod',      // ✅ 送料タイプ
            'shipping_fee' => 'nullable|integer|min:0|required_if:shipping_type,cod', // ✅ 送料金額
            'room_id' => 'nullable|exists:rooms,id',
        ]);
        

        $product = Product::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock ?? 0,
            'auction_type' => $request->auction_type,
            'min_price' => in_array($request->auction_type, ['auction', 'reverse']) ? $request->min_price : null,
            'start_at' => $request->start_at,
            'end_at' => $request->end_at,
            'category_id' => $request->category_id,
            'shipping_type' => $request->shipping_type,
            'shipping_fee' => $request->shipping_fee ?? 0,
            'room_id' => $request->room_id,
        ]);
        

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $product->images()->create(['path' => Storage::url($path)]);
            }
        }

        return back()->with('success', '商品を登録しました');
    }

    public function update(Request $request, Product $product)
    {
        if ($product->user_id !== auth()->id()) abort(403);
    
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'auction_type' => 'required|in:none,auction,reverse',
            'min_price' => 'nullable|required_if:auction_type,auction,reverse|integer|min:1',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'category_id' => 'required|exists:categories,id',  // ✅ カテゴリー
            'shipping_type' => 'required|in:included,cod',      // ✅ 送料タイプ
            'shipping_fee' => 'nullable|integer|min:0|required_if:shipping_type,cod', // ✅ 送料金額
            'room_id' => 'nullable|exists:rooms,id',
        ]);
    
        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock ?? 0,
            'auction_type' => $request->auction_type,
            'min_price' => in_array($request->auction_type, ['auction', 'reverse']) ? $request->min_price : null,
            'start_at' => $request->start_at,
            'end_at' => $request->end_at,
            'category_id' => $request->category_id,
            'shipping_type' => $request->shipping_type,
            'shipping_fee' => $request->shipping_fee ?? 0,
            'room_id' => $request->room_id,
        ]);
    
        return back()->with('success', '商品を更新しました');
    }

    public function destroy(Product $product)
    {
        if ($product->user_id !== auth()->id()) abort(403);
        Sale::where('product_id', $product->id)->delete();
        $product->delete();

        return back()->with('success', '商品を削除しました');
    }
    
    public function show($id)
    {
        $product = Product::with([
            'user',
            'images',
            'sales' => function ($query) {
                $query->where('status', 'approved')->with('room.user');
            }
        ])
        ->withCount('sales')
        ->findOrFail($id);

        $streamingRooms = collect();
        try {
            $response = Http::get('https://moon.timesfun.net:8443/status');
            if ($response->successful()) {
                $streamingRooms = collect($response->json());
            }
        } catch (\Exception $e) {
            \Log::warning('⚠️ 配信ステータス取得失敗: ' . $e->getMessage());
        }

        // ✅ ログインしていない場合は空コレクション
        $myRooms = auth()->check()
            ? auth()->user()->rooms()->latest('start')->get()
            : collect();

        return Inertia::render('Products/ProductDetail', [
            'product' => $product,
            'streamingRooms' => $streamingRooms,
            'myRooms' => $myRooms,
        ]);
    }

    public function addSeller(Request $request, Product $product)
    {
        $product->loadMissing('sellers');
        if (!$product->sellers->contains($request->user_id)) {
            $product->sellers()->attach($request->user_id);
        }
        return back()->with('success', '販売者を追加しました。');
    }

    public function removeSeller(Product $product, User $user)
    {
        $product->sellers()->detach($user->id);
        return back()->with('success', '販売者を削除しました。');
    }

    
}

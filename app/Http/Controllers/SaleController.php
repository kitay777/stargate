<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use Illuminate\Support\Facades\Log;

class SaleController extends Controller
{
    public function apply(Request $request)
    {
        Log::info('販売申請', [
            'user_id' => auth()->id(),
            'product_id' => $request->input('product_id'),
            'room_id' => $request->input('room_id'),
        ]);
        $validated = $request->merge($request->json()->all())->validate([
            'product_id' => 'required|exists:products,id',
            'room_id'    => 'required|exists:rooms,id',
        ]);
        

        if (Sale::where('product_id', $validated['product_id'])->where('room_id', $validated['room_id'])->exists()) {
            return back()->withErrors(['この商品はすでにこのルームで申請されています。']);
        }

        Sale::create([
            'product_id' => $validated['product_id'],
            'room_id'    => $validated['room_id'],
            'user_id'    => auth()->id(),
            'quantity'   => 1,
            'price'      => $request->input('price', 0), // UIから送る場合
            'shipping_price' => $request->input('shipping_price', 0),
            'fee'        => 0,
            'status'     => 'pending',
        ]);

        return back()->with('success', '販売申請を送信しました。');
    }
    public function approve(Sale $sale)
    {
        $sale->status = 'approved';
        $sale->save();

        return back()->with('success', '申請を承認しました。');
    }

    public function reject(Sale $sale)
    {
        $sale->status = 'rejected';
        $sale->save();

        return back()->with('success', '申請を却下しました。');
    }

}


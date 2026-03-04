<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TipImage;
use Illuminate\Support\Facades\Storage;

class AdminGiftController extends Controller
{
    public function index()
    {
        return inertia('Admin/Gifts/Index', [
            'gifts' => TipImage::orderBy('sort')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|integer|min:1',
            'image' => 'required|image'
        ]);

        $path = $request->file('image')->store('gifts', 'public');

        TipImage::create([
            'name' => $request->name,
            'price' => $request->price,
            'image_path' => $path,
            'is_active' => true,
            'sort' => 0,
        ]);

        return back();
    }

    public function toggle(TipImage $gift)
    {
        $gift->update([
            'is_active' => !$gift->is_active
        ]);

        return back();
    }

    public function update(Request $request, TipImage $gift)
    {
        $gift->update($request->only(['name','price','sort']));
        return back();
    }

    public function destroy(TipImage $gift)
    {
        Storage::disk('public')->delete($gift->image_path);
        $gift->delete();
        return back();
    }
}

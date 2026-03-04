<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BackgroundController extends Controller
{
    //
    // app/Http/Controllers/BackgroundController.php
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:204800',
        ]);

        $user = $request->user();
        $path = $request->file('image')->storeAs("public/backgrounds", "{$user->id}.jpg");

        return response()->json(['url' => Storage::url("backgrounds/{$user->id}.jpg")]);
    }

}

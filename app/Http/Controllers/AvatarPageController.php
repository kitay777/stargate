<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class AvatarPageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return Inertia::render('Avatar/Select', [
            'avatars' => $user->avatars()->latest()->get(),
            'currentAvatarId' => $user->avatar_id,
        ]);
    }
}

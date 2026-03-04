<?php

namespace App\Http\Controllers\Agency\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return inertia('Agency/Login');
    }



public function login(Request $request)
{
    Log::info('AGENCY LOGIN ATTEMPT', [
        'email' => $request->input('email'),
        'password_present' => $request->filled('password'),
        'password_length' => Str::length($request->input('password')),
    ]);

    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    // ① ユーザー取得を直接やる
    $provider = Auth::guard('agency')->getProvider();

    $user = $provider->retrieveByCredentials([
        'email' => $credentials['email'],
    ]);

    Log::info('AGENCY USER', [
        'exists' => (bool) $user,
        'id' => $user?->id,
        'password_hash' => $user?->password,
    ]);

    // ② パスワード検証を直接やる
    if ($user) {
        Log::info('PASSWORD CHECK', [
            'result' => Hash::check($credentials['password'], $user->password),
        ]);
    }

    // ③ ここで return してOK
    return response('check log', 200);
}



    public function logout(Request $request)
    {
        Auth::guard('agency')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('agency.login'));
    }
}

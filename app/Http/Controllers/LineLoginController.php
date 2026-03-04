<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class LineLoginController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('line')
            ->scopes(['openid', 'profile'])
            ->redirect();
    }

    public function callback()
    {
        Log::info('LINEログインコールバック開始');
        $lineUser = Socialite::driver('line')->stateless()->user();
        $user = User::where('line_user_id', $lineUser->getId())->first();

        Log::info('LINEログイン成功', [
            'line_user_id' => $lineUser->getId(),
            'name' => $lineUser->getName(),
        ]);
        if (!$user) {
            $user = User::create([
                'name'         => $lineUser->getName() ?? 'LINEユーザー',
                'email'        => null,
                'line_user_id' => $lineUser->getId(),
                'password'     => null,
                'is_line_friend' => true,
            ]);
        } else {
            $user->update([
                'is_line_friend' => true,
            ]);
        }
        $user = User::where('line_user_id', $lineUser->getId())->first();



        Auth::login($user);

        if (!$user->is_line_friend) {
            return redirect('/line-friend-required');
        }

        return redirect('/dashboard');
    }
}

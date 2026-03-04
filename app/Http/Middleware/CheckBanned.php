<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckBanned
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // BAN中判定
            if ($user->is_banned) {

                // 期限付きBANチェック
                if ($user->ban_until && now()->gt($user->ban_until)) {
                    // 期限過ぎたら自動解除
                    $user->update([
                        'is_banned' => false,
                        'ban_until' => null,
                        'ban_reason' => null,
                    ]);
                } else {
                    Auth::logout();
                    return redirect('/login')
                        ->withErrors(['email' => 'このアカウントは利用停止中です。']);
                }
            }
        }

        return $next($request);
    }
}

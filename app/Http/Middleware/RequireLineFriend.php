<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PgSql\Lob;
use Illuminate\Support\Facades\Log;

class RequireLineFriend
{
    public function handle(Request $request, Closure $next)
    {
        Log::debug('[DEBUG] RequireLineFriend middleware.');
        // LINEログイン関連は除外
        if ($request->is('auth/line*')) {
            return $next($request);
        }
Log::debug('[DEBUG] User authenticated: ' . Auth::check());
        if (!Auth::check()) {
            return $next($request);
        }
Log::debug('[DEBUG] Fetching fresh user data.');
        $user = Auth::user()->fresh();
Log::debug('User ID: ' . $user->id . ', is_line_friend: ' . ($user->is_line_friend ? 'true' : 'false'));
        if ($user->is_line_friend) {

            if ($request->is('line-friend-required')) {
                return redirect('/dashboard');
            }

            return $next($request);
        }

        if (!$request->is('line-friend-required')) {
            return redirect('/line-friend-required');
        }

        return $next($request);
    }
}

<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Auth;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Fortify::authenticateUsing(function (Request $request) {

            // =====================
            // Agency ログイン
            // =====================
            if ($request->is('agency/login')) {
                if (Auth::guard('agency')->attempt([
                    'email' => $request->email,
                    'password' => $request->password,
                ], false)) {
                    return Auth::guard('agency')->user();
                }
                return null;
            }

            // =====================
            // User ログイン
            // =====================
            if (Auth::attempt([
                'email' => $request->email,
                'password' => $request->password,
            ], false)) {
                return Auth::user();
            }

            return null;
        });
    }
}

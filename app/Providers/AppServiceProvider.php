<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\UrlGenerator;
use Inertia\Inertia;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Fortify;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Line\LineExtendSocialite;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(UrlGenerator $url): void
    {
        // HTTPS 強制
        $url->forceScheme('https');

        \Event::listen(
            SocialiteWasCalled::class,
            LineExtendSocialite::class . '@handle'
        );
        // 🔴 Jetstream / Fortify 用 login RateLimiter（必須）
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(
                ($request->input('email') ?? 'guest') . '|' . $request->ip()
            );
        });


        // Inertia 共通 share
        Inertia::share([
            'auth' => fn() => [
                'user' => auth()->user(),
                'user_type' => auth()->check() ? auth()->user()->type : null,
            ],
            'csrf_token' => csrf_token(),
            'flash' => function () {
                return session()->only(['success', 'error']);
            },
        ]);
    }
}

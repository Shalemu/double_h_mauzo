<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Mobile login: 5 tries a minute per account per IP. Counting per
        // account (not only per IP) matters because a shop's staff share one
        // Wi-Fi IP — one person's typos must not lock out everyone else.
        RateLimiter::for('api-login', fn (Request $request) => Limit::perMinute(5)
            ->by(strtolower((string) $request->input('username')) . '|' . $request->ip()));
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

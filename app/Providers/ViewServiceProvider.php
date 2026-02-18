<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Shops;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // Pass the first shop to all views that include sidebar
        View::composer('components.sidebar', function ($view) {
            $shop = Shops::first(); // or Auth::user()->shop if multi-tenant
            $view->with('shop', $shop);
        });
    }
}

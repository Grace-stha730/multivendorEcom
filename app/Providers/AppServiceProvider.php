<?php

namespace App\Providers;

use App\Models\Cart_items;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
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
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $cartCount = 0;
            $wishlistCount = 0;
            if (Auth::guard('web')->check()) {
                $cartCount = Cart_items::whereHas('cart', function ($q) {
                    $q->where('user_id', Auth::guard('web')->id());
                })->count();
                $wishlistCount = Wishlist::where('user_id', Auth::guard('web')->id())->count();
            }
            $view->with('cartCount', $cartCount)->with('wishlistCount', $wishlistCount);
        });
    }
}

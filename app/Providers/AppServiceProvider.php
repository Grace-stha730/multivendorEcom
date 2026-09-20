<?php

namespace App\Providers;

use App\Models\Cart_items;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
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
        // @authorizeUser('shop-view', 'admin') ... @endauthorizeUser  (guard optional)
        Blade::if('authorizeUser', fn (string $permission, ?string $guard = null) => authorizeUserCheck($permission, $guard));

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

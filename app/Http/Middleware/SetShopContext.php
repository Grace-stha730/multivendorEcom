<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applied to the shop-user route group. Guarantees every request carries a shop
 * (shop_users.shop_id) and exposes it as $request->attributes 'shop_id'.
 * Queries are scoped with Model::forCurrentShop() (App\Models\Concerns\BelongsToShop).
 */
class SetShopContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = config('access.guards.shop_user');
        $shopId = Auth::guard($guard)->user()?->shop_id;

        if (!$shopId) {
            Auth::guard($guard)->logout();

            return redirect()->route('shop-user.login')->with('error', 'Your account is not linked to a shop.');
        }

        $request->attributes->set('shop_id', $shopId);

        return $next($request);
    }
}

<?php

use Illuminate\Support\Facades\Auth;

if (!function_exists('authorizeUserCheck')) {
    /**
     * Generic permission check: "can the currently logged-in staff user do X?"
     *
     * Works for both pools (admin guard and shop_user guard) and never looks at role names.
     * Pass $guard to pin the check to one pool (recommended on routes that belong to a single
     * pool); leave it null to accept whichever configured guard is logged in and holds the permission.
     */
    function authorizeUserCheck(string $permission, ?string $guard = null): bool
    {
        $guards = $guard ? [$guard] : array_values(config('access.guards'));

        foreach ($guards as $name) {
            $user = Auth::guard($name)->user();

            // checkPermissionTo() returns false (instead of throwing) if the permission doesn't exist for that guard.
            if ($user && method_exists($user, 'checkPermissionTo') && $user->checkPermissionTo($permission, $name)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('currentShopId')) {
    /** The shop the logged-in shop_user belongs to, or null. The single source of truth for shop scoping. */
    function currentShopId(): ?int
    {
        return Auth::guard(config('access.guards.shop_user'))->user()?->shop_id;
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route middleware: authorize:<permission>[|<permission>...][,<guard>]
 *   ->middleware('authorize:shop-view,admin')
 *   ->middleware('authorize:order-view|delivery-view,admin')   // any one of them is enough
 *   ->middleware('authorize:staff-assign-role,shop_user')
 * Runs after the authentication middleware (admin / shop_user) on the route.
 */
class AuthorizePermission
{
    public function handle(Request $request, Closure $next, string $permission, ?string $guard = null): Response
    {
        $allowed = collect(explode('|', $permission))
            ->contains(fn (string $p) => authorizeUserCheck(trim($p), $guard));

        abort_unless($allowed, 403, 'You do not have permission to do that.');

        return $next($request);
    }
}

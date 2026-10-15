<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Checks admin-panel requests against config/panel_routes.php, so each page
 * and action needs the matching permission on the user's role.
 */
class PanelPermissionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('web')->user();
        $routeName = optional($request->route())->getName();

        if (!$user || !$routeName || $user->hasFullAccess()) {
            return $next($request);
        }

        $required = self::permissionFor($routeName);
        if ($required === null) {
            return $next($request);
        }

        foreach ((array) $required as $permission) {
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }

        // Landing on a dashboard you can't see: go to the first page you can.
        if ($routeName === 'dashboard.admin' && $request->isMethod('get') && ($home = self::homeFor($user))) {
            return redirect($home);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Your role does not have permission to do this.'], 403);
        }

        abort(403, 'Your role does not have permission to do this.');
    }

    public static function permissionFor(string $routeName)
    {
        foreach (config('panel_routes') as $pattern => $permission) {
            if (Str::is($pattern, $routeName)) {
                return $permission;
            }
        }

        return null;
    }

    /**
     * First page in sidebar order this user may open.
     */
    public static function homeFor($user): ?string
    {
        $pages = [
            'dashboard.admin', 'admin.orders.index', 'admin.pos.index', 'admin.sales.index', 'products.index',
            'purchases.index', 'suppliers.index', 'customers.index', 'admin.expenses', 'staff.manage.index',
            'dashboard.shop', 'stock-transfers.index', 'admin.issues', 'profile',
        ];

        foreach ($pages as $page) {
            $permission = self::permissionFor($page);
            if ($permission === null || collect((array) $permission)->contains(fn ($p) => $user->hasPermission($p))) {
                return route($page);
            }
        }

        return null;
    }
}

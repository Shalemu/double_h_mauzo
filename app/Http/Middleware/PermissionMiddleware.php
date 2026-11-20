<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware
{
    /**
     * Usage: ->middleware('permission:orders.create')
     *        ->middleware('permission:pos.sell,products.view')  (any one is enough)
     *
     * Staff need the permission on their role; admin-panel users go through
     * Users::hasPermission (full-access accounts always pass). Works for web
     * sessions and API tokens alike (Auth::user() is whoever is signed in).
     */
    public function handle(Request $request, Closure $next, string ...$permissions)
    {
        $user = Auth::user();
        $allowed = $user && collect($permissions)->contains(fn ($p) => $user->hasPermission($p));

        if (!$allowed) {
            abort(403, $user?->isStaff()
                ? 'Your role does not have permission to access this page.'
                : 'You are not authorized to access this page.');
        }

        return $next($request);
    }
}

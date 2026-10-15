<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware
{
    /**
     * Usage: ->middleware('permission:orders.create')
     * Staff need the permission on their role; admin-panel users go through
     * Users::hasPermission (full-access accounts always pass).
     */
    public function handle(Request $request, Closure $next, string $permission)
    {
        if (Auth::guard('staff')->check()) {
            if (!Auth::guard('staff')->user()->hasPermission($permission)) {
                abort(403, 'Your role does not have permission to access this page.');
            }
            return $next($request);
        }

        $user = Auth::user();
        if (!$user || !method_exists($user, 'hasPermission') || !$user->hasPermission($permission)) {
            abort(403, 'You are not authorized to access this page.');
        }

        return $next($request);
    }
}

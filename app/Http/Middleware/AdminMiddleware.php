<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Admin/manager roles, super users, or any role given admin-panel
        // permissions. What each may do is checked by PanelPermissionMiddleware.
        if (!$user || !method_exists($user, 'isAdminTier') || !$user->isAdminTier()) {
            abort(403, 'You are not authorized to access this page.');
        }

        return $next($request);
    }
}

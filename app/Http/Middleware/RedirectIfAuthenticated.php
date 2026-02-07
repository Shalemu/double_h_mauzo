<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
public function handle($request, Closure $next, $guard = null)
{
    // Admin only
    if (Auth::guard('web')->check()) {
        return redirect()->route('dashboard.admin');
    }

    // Staff only
    if (Auth::guard('staff')->check()) {
        return redirect()->route('staff.dashboard');
    }

    return $next($request);
}


}

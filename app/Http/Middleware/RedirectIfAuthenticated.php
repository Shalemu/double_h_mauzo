<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle($request, Closure $next, ...$guards)
    {
        if ($user = Auth::user()) {
            return redirect()->route($user->isStaff() ? 'staff.dashboard' : 'dashboard.admin');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Admins have their own panel; the POS pages are for shop staff only.
        if (!$user->isStaff()) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}

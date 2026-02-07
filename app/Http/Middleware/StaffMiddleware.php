<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check if staff is logged in using the staff guard
        if (!Auth::guard('staff')->check()) {
            return redirect()->route('login');
        }

        // Optional: ensure it's NOT admin (role_id > 2)
        $staff = Auth::guard('staff')->user();
        if ($staff->role_id <= 2) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}

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

        if (!$user || !in_array($user->role_id, [1, 2])) { // 1=Super Admin, 2=Admin
            abort(403, 'You are not authorized to access this page.');
        }

        return $next($request);
    }
}

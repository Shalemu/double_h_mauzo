<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
  public function handle(Request $request, Closure $next, ...$roles)
{
    $user = auth()->user();
    if (!$user) {
        abort(403);
    }

    $userRole = strtolower(optional($user->role)->name);

    $allowed = array_map('strtolower', $roles);

    if (!in_array($userRole, $allowed)) {
        abort(403);
    }

    return $next($request);
}

}

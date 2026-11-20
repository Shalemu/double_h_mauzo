<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Every /api request is answered in JSON.
 *
 * Without this, a request without "Accept: application/json" that fails
 * authentication is redirected to the web login page (HTML), which a mobile
 * app can't read. With it, the app always gets e.g. 401 {"message": "..."}.
 */
class ForceJsonResponse
{
    public function handle(Request $request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}

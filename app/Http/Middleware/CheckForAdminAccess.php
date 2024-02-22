<?php

namespace App\Http\Middleware;

use Closure;

class CheckForAdminAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // @todo Check if current user has admin access.

        return $next($request);
    }
}

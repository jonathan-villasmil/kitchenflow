<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleRedirectMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && request()->is('admin')) {
            if ($user->hasRole('camarero')) {
                return redirect()->route('pos');
            }
            if ($user->hasRole('cocinero')) {
                return redirect()->route('kds');
            }
        }

        return $next($request);
    }
}

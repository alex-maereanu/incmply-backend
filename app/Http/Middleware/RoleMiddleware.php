<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role, $permission = null)
    {
        if (!$request->user()->hasRole($role)) {
            return response()->json(['error' => __('auth.accessDenied')], 403);
        }

        if ($permission !== null && !$request->user()->can($permission)) {
            return response()->json(['error' => __('auth.accessDenied')], 403);
        }

        return $next($request);
    }
}

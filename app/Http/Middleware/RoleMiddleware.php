<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $role = null)
    {
        if (!$role) {
            return response()->json(['message' => 'Role not provided'], 400);
        }

        if (auth()->check() && auth()->user()->role === $role) {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }
}

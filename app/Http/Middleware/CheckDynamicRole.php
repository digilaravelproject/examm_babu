<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDynamicRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. URL se role uthao (instructor, manager, etc.)
        $routeRole = $request->route('role');

        // 2. Admin aur Student ko yahan aane se roko (Unki file alag hai)
        // if (in_array($routeRole, ['admin', 'student'])) {
        //     abort(404);
        // }

        // 3. Check karo: Kya logged-in user ke paas yeh role hai?
        // (Yeh Spatie package ka function hai)
        if (! $request->user()->hasRole($routeRole)) {
            abort(403, 'Unauthorized Access');
        }

        return $next($request);
    }
}

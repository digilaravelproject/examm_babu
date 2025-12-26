<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSyllabusSelected
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
   public function handle(Request $request, Closure $next): Response
{
    // 1. Agar User Login nahi hai, to rokne ki zarurat nahi (Auth middleware sambhal lega)
    if (!Auth::check()) {
        return $next($request);
    }

    // 2. Wo routes jaha bina syllabus ke ja sakte hain (Loop bachane ke liye)
    $allowedRoutes = [
        'student.change_syllabus',
        'student.update_syllabus',
        'logout'
    ];

    if ($request->routeIs($allowedRoutes)) {
        return $next($request);
    }

    // 3. Agar Cookie nahi hai -> Redirect
    if (!$request->cookie('category_id')) {
        return redirect()->route('student.change_syllabus');
    }

    return $next($request);
}
}

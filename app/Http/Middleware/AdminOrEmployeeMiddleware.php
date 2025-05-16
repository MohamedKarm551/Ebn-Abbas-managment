<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminOrEmployeeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && (Auth::user()->role === 'Admin' || Auth::user()->role === 'employee')) {
            return $next($request);
        }
        
        return redirect('/')->with('error', 'ليس لديك صلاحية الوصول إلى هذه الصفحة');
    }
}
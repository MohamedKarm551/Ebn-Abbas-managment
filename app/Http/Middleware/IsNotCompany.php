<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // ضروري عشان نستخدم Auth

class IsNotCompany
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // بنتحقق لو المستخدم مسجل دخوله ودوره مش 'Company'
        if (Auth::check() && Auth::user()->role !== 'Company') {
            // لو الشرط صح، كمل الريكويست عادي للروت المطلوب
            return $next($request);
        }

        // لو الشرط مش صح (يعني المستخدم شركة أو مش مسجل دخوله أصلاً)
        // رجعه لصفحة الهوم مثلاً مع رسالة خطأ
        // أو ممكن ترجعه لصفحة اللوجين لو مش مسجل دخول
        // أو ممكن تعمل abort(403) لو عايز تظهر صفحة "غير مصرح لك"
        return redirect('/home')->with('error', 'غير مصرح لك بالوصول لهذه الصفحة.');
        // أو ممكن: abort(403, 'غير مصرح لك بالوصول.');
    }
}

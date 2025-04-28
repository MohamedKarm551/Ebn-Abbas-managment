<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // *** لازم نضيف دي عشان نستخدم Auth ***
use Symfony\Component\HttpFoundation\Response;

class IsCompany
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. بنشوف لو المستخدم مسجل دخوله أصلاً
        // 2. بنشوف لو الـ role بتاع المستخدم ده هو 'Company' (أو أي قيمة بتستخدمها لتمييز الشركات)
        if (Auth::check() && Auth::user()->role === 'Company') {
            // لو الشرطين اتحققوا، بنعدي الـ request يكمل عادي للدالة اللي بعدها (الـ Controller)
            return $next($request);
        }

        // لو المستخدم مش مسجل دخوله أو مش شركة، بنمنعه ونرجعله خطأ 403 (Forbidden)
        // ممكن بدل abort توجهه لصفحة تانية زي صفحة الدخول مثلاً
        // return redirect('/login')->with('error', 'يجب تسجيل الدخول كشركة للوصول لهذه الصفحة.');
        abort(403, 'غير مصرح لك بالوصول لهذه الصفحة.');
    }
}

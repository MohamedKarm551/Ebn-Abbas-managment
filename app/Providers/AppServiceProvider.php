<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class AppServiceProvider extends ServiceProvider
{
    // وظيفة الكلاس ده : 
    // 1. تسجيل الخدمات في التطبيق.
    // 2. تحميل أي خدمات أو إعدادات خاصة بالتطبيق.
    // 3. إضافة أي خدمات أو إعدادات خاصة بالـ View.
    // 4. إضافة أي خدمات أو إعدادات خاصة بالـ Auth.
    // 5. إضافة أي خدمات أو إعدادات خاصة بالـ Middleware.
    // 6. إضافة أي خدمات أو إعدادات خاصة بالـ Console.
    // 7. إضافة أي خدمات أو إعدادات خاصة بالـ Routes.
    // 8. إضافة أي خدمات أو إعدادات خاصة بالـ Config.
    // 9. إضافة أي خدمات أو إعدادات خاصة بالـ Events.
    // 10. إضافة أي خدمات أو إعدادات خاصة بالـ Listeners.
    // 11. إضافة أي خدمات أو إعدادات خاصة بالـ Jobs.
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
{
    View::composer('*', function ($view) {
        if (Auth::check()) { // فقط لو المستخدم مسجل دخول
            $userId = Auth::user()->id;
            // جلب آخر 5 إشعارات غير مقروءة للمستخدم الحالي
            $lastNotifications = Notification::where('user_id', $userId)
                                            // ->where('is_read', false) // يمكنك إضافة هذا السطر لو أردت عرض غير المقروء فقط
                                            ->latest() // الأحدث أولاً
                                            ->take(5)  // آخر 5
                                            ->get();
            // جلب عدد الإشعارات غير المقروءة للمستخدم الحالي
            $unreadNotificationsCount = Notification::where('user_id', $userId)
                                                    ->where('is_read', false)
                                                    ->count();

            $view->with('lastNotifications', $lastNotifications);
            $view->with('unreadNotificationsCount', $unreadNotificationsCount); // تمرير العدد للفيو
        } else {
            // لو المستخدم مش مسجل دخول، نمرر مجموعات فارغة
            $view->with('lastNotifications', collect());
            $view->with('unreadNotificationsCount', 0);
        }
    });
    // *** نهاية التعديل ***
}
}
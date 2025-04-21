<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class AppServiceProvider extends ServiceProvider
{
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
        // هذا يعني أن الكود سيعمل مع كل View في الموقع (* تعني جميع الصفحات).

        if (Auth::check() && Auth::user()->role === 'Admin') {
            // يتحقق إذا كان المستخدم مسجل دخول (auth) وأنه أدمن فقط.

            $view->with('lastNotifications', Notification::latest()->take(5)->get());
        } else {
            $view->with('lastNotifications', collect());
        }
    });
}
}

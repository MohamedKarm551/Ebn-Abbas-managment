<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
        'isCompany' => \App\Http\Middleware\IsCompany::class, // <-- عرفنا لارافيل إن isCompany بتشاور على الكلاس ده
        // 'isAdminOrEmployee' => \App\Http\Middleware\IsAdminOrEmployee::class, // <-- ضيف ده كمان بالمرة لو مش موجود عشان مجموعة الأدمن
        'isNotCompany' => \App\Http\Middleware\IsNotCompany::class, // <-- السطر الجديد اللي ضفناه



    ];
}
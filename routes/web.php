<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\CompanyAvailabilityController;
use App\Http\Controllers\CompanyPaymentController;
use App\Http\Controllers\LandTripController;
use App\Http\Controllers\CompanyLandTripController;
use App\Http\Controllers\TripTypeController;
use App\Http\Controllers\HotelRoomController;
use App\Http\Controllers\BookingOperationReportController;
use App\Http\Controllers\AdminTransactionController;
use App\Http\Controllers\LandTripsAgentPaymentController;
use App\Models\User;
use Jenssegers\Agent\Agent;
use App\Models\Notification;
use App\Models\Company;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// الصفحة الرئيسية
Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

// إلغاء التسجيل العام من خلال Auth::routes
Auth::routes(['register' => false]);

// مسارات تسجيل الدخول المخصصة
Route::get('/login', function () {
    return redirect('/');
})->name('login');

Route::post('/manual-login', function (Request $request) {
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials, $request->filled('remember'))) {
        $user = Auth::user();
        $agent = new Agent();

        // جمع معلومات الجهاز للتسجيل
        $device = $agent->device();
        $platform = $agent->platform();
        $browser = $agent->browser();
        $ip = $request->ip();

        if ($user) {
            // إرسال إشعار تسجيل الدخول للمدراء
            $adminUsers = User::where('role', 'Admin')->get();
            foreach ($adminUsers as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'تسجيل دخول',
                    'message' => "تم تسجيل دخول المستخدم {$user->name} من جهاز {$device} ونظام {$platform} ومتصفح {$browser} من IP: {$ip}",
                    'type' => 'login',
                ]);
            }
        }

        // التوجيه حسب نوع المستخدم
        if ($user->role === 'Company') {
            return redirect('/company/land-trips');
        } else {
            return redirect('/bookings');
        }
    }

    return back()->withErrors(['email' => 'بيانات الدخول غير صحيحة'])->withInput();
})->name('manual.login');

Route::post('/logout', function (Request $request) {
    $user = Auth::user();
    $agent = new Agent();

    // جمع معلومات الجهاز للتسجيل
    $device = $agent->device();
    $platform = $agent->platform();
    $browser = $agent->browser();
    $ip = $request->ip();

    if ($user) {
        // إرسال إشعار تسجيل الخروج للمدراء
        $adminUsers = User::where('role', 'Admin')->get();
        foreach ($adminUsers as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'title' => 'تسجيل خروج',
                'message' => "تم تسجيل خروج المستخدم {$user->name} من جهاز {$device} ونظام {$platform} ومتصفح {$browser} من IP: {$ip}",
                'type' => 'logout',
            ]);
        }
    }

    Auth::logout();
    return redirect('/');
})->name('logout');

// مسار الحماية من أدوات المطور
Route::post('/devtools-logout', function () {
    if (Auth::check()) {
        Notification::create([
            'user_id' => Auth::id(),
            'message' => "محاولة فحص الصفحة أو التعديل عبر أدوات المطور.",
            'type' => 'تنبيه أمني',
        ]);
        Auth::logout();
    }
    return response()->json(['status' => 'ok']);
})->name('devtools.logout');

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Booking Management Routes
    |--------------------------------------------------------------------------
    */

    // مسارات تصدير الحجوزات
    Route::get('/bookings/export', [BookingsController::class, 'exportBookings'])->name('bookings.export');
    Route::get('/bookings/export-all', [BookingsController::class, 'exportAllBookings'])->name('bookings.export.all');

    // مسار الاقتراحات والبحث التلقائي
    Route::get('/bookings/autocomplete', [BookingsController::class, 'autocomplete'])->name('bookings.autocomplete');

    // مسارات الحجوزات الأساسية
    Route::get('/bookings', [BookingsController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', [BookingsController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingsController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/details/{hotelId}', [BookingsController::class, 'details'])->name('bookings.details');
    Route::get('/bookings/{id}/edit', [BookingsController::class, 'edit'])->name('bookings.edit');
    Route::put('/bookings/{id}/update', [BookingsController::class, 'update'])->name('bookings.update');
    Route::get('/bookings/{id}/edits', [BookingsController::class, 'getEdits']);
    Route::get('/bookings/{id}/voucher', [BookingsController::class, 'voucher'])->name('bookings.voucher');
    Route::delete('/bookings/{id}', [BookingsController::class, 'destroy'])->name('bookings.destroy');
    Route::get('/bookings/{id}', [BookingsController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/record-payment', [BookingsController::class, 'recordPayment'])->name('bookings.record-payment');
    // ===== مسارات المتابعة المالية للحجوزات =====
    // مجموعة مسارات شاملة للمتابعة المالية مع توضيح كل مسار
    Route::prefix('bookings/{booking}/financial-tracking')->name('bookings.financial-tracking.')->group(function () {

        // عرض المتابعة المالية للحجز (GET)
        // يستخدم لتحميل بيانات المتابعة المالية الموجودة أو إنشاء قالب جديد
        Route::get('/', [App\Http\Controllers\FinancialTrackingController::class, 'show'])
            ->name('show')
            ->where('booking', '[0-9]+'); // التأكد من أن معرف الحجز رقم صحيح

        // حفظ أو تحديث المتابعة المالية (POST)
        // يستخدم لإنشاء متابعة مالية جديدة أو تحديث الموجودة
        Route::post('/', [App\Http\Controllers\FinancialTrackingController::class, 'store'])
            ->name('store')
            ->where('booking', '[0-9]+');

        // تحديث المتابعة المالية (PUT) - مسار اختياري إضافي
        // يمكن استخدامه للتحديثات المباشرة
        Route::put('/', [App\Http\Controllers\FinancialTrackingController::class, 'store'])
            ->name('update')
            ->where('booking', '[0-9]+');

        // حذف المتابعة المالية (DELETE)
        // يستخدم لحذف المتابعة المالية نهائياً (للمدراء فقط)
        Route::delete('/', [App\Http\Controllers\FinancialTrackingController::class, 'destroy'])
            ->name('destroy')
            ->where('booking', '[0-9]+')
            ->middleware('can:delete,financial-tracking'); // إضافة تحقق من الصلاحيات
    });

    // ===== مسارات إضافية للإحصائيات والتقارير =====
    Route::prefix('financial-tracking')->name('financial-tracking.')->group(function () {

        // إحصائيات المتابعة المالية العامة
        Route::get('/statistics', [App\Http\Controllers\FinancialTrackingController::class, 'statistics'])
            ->name('statistics')
            ->middleware('can:view-financial-statistics'); // صلاحية عرض الإحصائيات

        // تقرير المتابعات المتأخرة
        Route::get('/overdue', [App\Http\Controllers\FinancialTrackingController::class, 'overdueReport'])
            ->name('overdue')
            ->middleware('can:view-financial-reports');

        // تقرير المتابعات عالية الأولوية
        Route::get('/high-priority', [App\Http\Controllers\FinancialTrackingController::class, 'highPriorityReport'])
            ->name('high-priority')
            ->middleware('can:view-financial-reports');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes (Non-Company Users Only)
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin')->name('admin.')->middleware([\App\Http\Middleware\IsNotCompany::class])->group(function () {

        // إدارة الموظفين
        Route::get('/employees', [AdminController::class, 'employees'])->name('employees');
        Route::post('/employees', [AdminController::class, 'storeEmployee'])->name('storeEmployee');
        Route::delete('/employees/{id}', [AdminController::class, 'deleteEmployee'])->name('deleteEmployee');
        Route::put('/employees/{id}', [AdminController::class, 'updateEmployee'])->name('updateEmployee');

        // إدارة الشركات
        Route::get('/companies', [AdminController::class, 'companies'])->name('companies');
        Route::post('/companies', [AdminController::class, 'storeCompany'])->name('storeCompany');
        Route::get('/companies/{id}/edit', [AdminController::class, 'editCompany'])->name('editCompany');
        Route::put('/companies/{id}', [AdminController::class, 'updateCompany'])->name('updateCompany');
        Route::delete('/companies/{id}', [AdminController::class, 'deleteCompany'])->name('deleteCompany');

        // إدارة الوكلاء
        Route::get('/agents', [AdminController::class, 'agents'])->name('agents');
        Route::post('/agents', [AdminController::class, 'storeAgent'])->name('storeAgent');
        Route::get('/agents/{id}/edit', [AdminController::class, 'editAgent'])->name('editAgent');
        Route::put('/agents/{id}', [AdminController::class, 'updateAgent'])->name('updateAgent');
        Route::delete('/agents/{id}', [AdminController::class, 'deleteAgent'])->name('deleteAgent');

        // إدارة الفنادق
        Route::get('/hotels', [HotelController::class, 'index'])->name('hotels');
        Route::post('/hotels', [HotelController::class, 'store'])->name('storeHotel');
        Route::delete('/hotels/{id}', [HotelController::class, 'destroy'])->name('deleteHotel');
        Route::get('/hotels/{id}/edit', [HotelController::class, 'edit'])->name('editHotel');
        Route::put('/hotels/{id}', [HotelController::class, 'update'])->name('updateHotel');

        // مسارات الأرشيف
        Route::get('/archived-bookings', [AdminController::class, 'archivedBookings'])->name('archived_bookings');
        Route::get('/archived-bookings/autocomplete', [AdminController::class, 'archivedAutocomplete'])->name('archived_bookings.autocomplete');
        Route::get('/archived-bookings/export', [AdminController::class, 'exportArchivedBookings'])->name('archived_bookings.export');

        // إدارة أنواع الغرف والإتاحات
        Route::resource('room_types', RoomTypeController::class);
        Route::resource('availabilities', AvailabilityController::class);
        // إضافة مسار سند القبض
        Route::get('/receipt-voucher', [ReportController::class, 'receiptVoucher'])->name('receipt.voucher');
        Route::post('/receipt-voucher/generate', [ReportController::class, 'generateReceiptVoucher'])->name('receipt.voucher.generate');
    });

    /*
    |--------------------------------------------------------------------------
    | Hotel Room Management Routes (Admin Only)
    |--------------------------------------------------------------------------
    */

    Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->group(function () {
        Route::get('/hotel-rooms', [HotelRoomController::class, 'index'])->name('hotel.rooms.index');
        Route::get('/hotel-rooms/hotel/{id}', [HotelRoomController::class, 'showHotel'])->name('hotel.rooms.hotel');
        Route::get('/hotel-rooms/{id}', [HotelRoomController::class, 'showRoom'])->name('hotel.rooms.show');
        Route::post('/hotel-rooms/create', [HotelRoomController::class, 'createRooms'])->name('hotel.rooms.create');
        Route::post('/hotel-rooms/assign', [HotelRoomController::class, 'assignRoom'])->name('hotel.rooms.assign');
        Route::get('/hotels/rooms/{room}/edit', [HotelRoomController::class, 'edit'])->name('hotel.rooms.edit');
        Route::patch('/hotels/rooms/{room}', [HotelRoomController::class, 'update'])->name('hotel.rooms.update');
        Route::delete('/hotels/rooms/{room}', [HotelRoomController::class, 'destroy'])->name('hotel.rooms.destroy');
        Route::patch('/hotel-rooms/end-assignment/{id}', [HotelRoomController::class, 'endAssignment'])->name('hotel.rooms.end-assignment');
        Route::post('/hotel-rooms/assign-multiple', [HotelRoomController::class, 'assignMultipleRooms'])->name('hotel.rooms.assign-multiple');
        Route::post('/hotel-rooms/add-guest', [HotelRoomController::class, 'addGuest'])->name('hotel.rooms.add-guest');
    });

    /*
    |--------------------------------------------------------------------------
    | Company Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('company')->name('company.')->group(function () {
        // عرض الإتاحات للشركة
        Route::get('/availabilities', [CompanyAvailabilityController::class, 'index'])->name('availabilities.index');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin-Only Reports and Financial Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware([\App\Http\Middleware\AdminMiddleware::class])->group(function () {

        // التقارير اليومية والمتقدمة
        Route::get('/reports/daily', [ReportController::class, 'daily'])->name('reports.daily');
        Route::get('/reports/advanced/{date?}', [ReportController::class, 'advanced'])->name('reports.advanced');

        // تقارير الشركات والوكلاء والفنادق
        Route::get('/reports/company/{id}/bookings', [ReportController::class, 'companyBookings'])->name('reports.company.bookings');
        Route::get('/reports/agent/{id}/bookings', [ReportController::class, 'agentBookings'])->name('reports.agent.bookings');
        Route::get('/reports/hotel/{id}/bookings', [ReportController::class, 'hotelBookings'])->name('reports.hotel.bookings');

        // إدارة الدفعات
        Route::post('/reports/company/payment', [ReportController::class, 'storePayment'])->name('reports.company.payment');
        Route::post('/reports/agent/payment', [ReportController::class, 'storeAgentPayment'])->name('reports.agent.payment');

        // عرض سجل المدفوعات
        Route::get('reports/company/{id}/payments', [ReportController::class, 'companyPayments'])->name('reports.company.payments');
        Route::get('reports/agent/{id}/payments', [ReportController::class, 'agentPayments'])->name('reports.agent.payments');

        // تعديل وحذف دفعات الشركات
        Route::get('reports/company/payment/{id}/edit', [ReportController::class, 'editCompanyPayment'])->name('reports.company.payment.edit');
        Route::put('reports/company/payment/{id}', [ReportController::class, 'updateCompanyPayment'])->name('reports.company.payment.update');
        Route::delete('reports/company/payment/{id}', [ReportController::class, 'destroyCompanyPayment'])->name('reports.company.payment.destroy');

        // تعديل وحذف دفعات الوكلاء
        Route::get('reports/agent/payment/{id}/edit', [ReportController::class, 'editAgentPayment'])->name('reports.agent.payment.edit');
        Route::put('reports/agent/payment/{id}', [ReportController::class, 'updateAgentPayment'])->name('reports.agent.payment.update');
        Route::delete('reports/agent/payment/{id}', [ReportController::class, 'destroyAgentPayment'])->name('reports.agent.payment.destroy');
        Route::post('/reports/agent/{agent}/discount', [ReportController::class, 'applyAgentDiscount'])
            ->name('reports.agent.discount');
        // جدول الفنادق 
        Route::get('/reports/hotels-ajax', [ReportController::class, 'getHotelsAjax'])->name('reports.hotels.ajax');
        // جدول جهات الحجز
        Route::get('/reports/agents-ajax', [ReportController::class, 'getAgentsAjax'])->name('reports.agents.ajax');

        // مخطط العلاقات
        Route::get('/network-graph', [ReportController::class, 'networkGraph'])->name('network.graph');
        Route::get('/network-data', [ReportController::class, 'getNetworkData'])->name('network.data');

        // المصاريف الشهرية
        Route::get('/admin/monthly-expenses', [App\Http\Controllers\MonthlyExpenseController::class, 'index'])->name('admin.monthly-expenses.index');
        // Route::get('/admin/monthly-expenses/create', [App\Http\Controllers\MonthlyExpenseController::class, 'create'])->name('admin.monthly-expenses.create'); // لإضافة أول سجل مصاريف
        Route::post('/admin/monthly-expenses', [App\Http\Controllers\MonthlyExpenseController::class, 'store'])->name('admin.monthly-expenses.store');
        Route::post('/admin/calculate-profit', [App\Http\Controllers\MonthlyExpenseController::class, 'calculateProfit'])->name('admin.calculate-profit');
        Route::get('/admin/monthly-expenses/{id}', [App\Http\Controllers\MonthlyExpenseController::class, 'show'])->name('admin.monthly-expenses.show');
        Route::get('/admin/monthly-expenses/{id}/edit', [App\Http\Controllers\MonthlyExpenseController::class, 'edit'])->name('admin.monthly-expenses.edit');
        Route::put('/admin/monthly-expenses/{id}', [App\Http\Controllers\MonthlyExpenseController::class, 'update'])->name('admin.monthly-expenses.update');
        Route::delete('/admin/monthly-expenses/{id}', [App\Http\Controllers\MonthlyExpenseController::class, 'destroy'])->name('admin.monthly-expenses.destroy');

        // المعاملات المالية
        Route::prefix('admin/transactions')->name('admin.transactions.')->group(function () {
            // المسارات الثابتة أولاً
            Route::get('/', [AdminTransactionController::class, 'index'])->name('index');
            Route::get('/create', [AdminTransactionController::class, 'create'])->name('create');
            Route::get('/api/exchange-rates', [AdminTransactionController::class, 'getExchangeRates'])->name('exchange-rates');
            Route::get('/export/excel', [AdminTransactionController::class, 'export'])->name('export');

            // مسارات التقارير
            Route::prefix('reports')->name('reports.')->group(function () {
                Route::get('/monthly', [AdminTransactionController::class, 'monthlyReport'])->name('monthly');
                Route::get('/yearly', [AdminTransactionController::class, 'yearlyReport'])->name('yearly');
            });

            // المسارات الديناميكية في النهاية
            Route::post('/', [AdminTransactionController::class, 'store'])->name('store');
            Route::get('/{transaction}', [AdminTransactionController::class, 'show'])->name('show');
            Route::get('/{transaction}/edit', [AdminTransactionController::class, 'edit'])->name('edit');
            Route::put('/{transaction}', [AdminTransactionController::class, 'update'])->name('update');
            Route::delete('/{transaction}', [AdminTransactionController::class, 'destroy'])->name('destroy');
        });



        // حفظ لقطات الشاشة والملفات
        Route::post('/save-screenshot', [ReportController::class, 'saveScreenshot']);
        Route::post('/save-pdf', [ReportController::class, 'savePDF']);

        //  مدفوعات الشركات والخصم
        Route::prefix('admin/company-payments')->name('admin.company-payments.')->group(function () {
            Route::get('/', [CompanyPaymentController::class, 'index'])->name('index');
            // ✅ إضافة مسار التقارير المالية
            Route::get('/reports', [CompanyPaymentController::class, 'reports'])->name('reports');
            Route::get('/reports/data', [CompanyPaymentController::class, 'data'])->name('reports.data');

            Route::get('/{company}', [CompanyPaymentController::class, 'show'])->name('show');
            Route::get('/{company}/bookings', [CompanyPaymentController::class, 'bookings'])->name('bookings'); // ✅ جديد

            Route::get('/{company}/create', [CompanyPaymentController::class, 'create'])->name('create');
            Route::post('/{company}', [CompanyPaymentController::class, 'store'])->name('store');
            Route::get('/{company}/{payment}/edit', [CompanyPaymentController::class, 'edit'])->name('edit');
            Route::put('/{company}/{payment}', [CompanyPaymentController::class, 'update'])->name('update');
            Route::delete('/{company}/{payment}', [CompanyPaymentController::class, 'destroy'])->name('destroy');
            Route::post('/{company}/apply-discount', [CompanyPaymentController::class, 'applyDiscount'])->name('apply-discount');
        });
        // مدفوعات الوكلاء للرحلات البرية
        Route::prefix('admin/land-trips-agent-payments')->name('admin.land-trips-agent-payments.')->group(function () {
            Route::get('/', [LandTripsAgentPaymentController::class, 'index'])->name('index');
            Route::get('/{agent}', [LandTripsAgentPaymentController::class, 'show'])->name('show');
            Route::get('/{agent}/create', [LandTripsAgentPaymentController::class, 'create'])->name('create');
            Route::post('/{agent}', [LandTripsAgentPaymentController::class, 'store'])->name('store');
            Route::get('/{agent}/{payment}/edit', [LandTripsAgentPaymentController::class, 'edit'])->name('edit');
            Route::put('/{agent}/{payment}', [LandTripsAgentPaymentController::class, 'update'])->name('update');
            Route::delete('/{agent}/{payment}', [LandTripsAgentPaymentController::class, 'destroy'])->name('destroy');
            Route::post('/{agent}/apply-discount', [LandTripsAgentPaymentController::class, 'applyDiscount'])->name('apply-discount');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Notification Routes (Non-Company Users)
    |--------------------------------------------------------------------------
    */

    Route::middleware([\App\Http\Middleware\IsNotCompany::class])->group(function () {
        Route::get('/admin/notifications', [AdminController::class, 'notifications'])->name('admin.notifications');
        Route::post('/admin/notifications/{id}/read', [AdminController::class, 'markNotificationRead'])->name('admin.notifications.markRead');
        Route::post('/admin/notifications/mark-all-read', [AdminController::class, 'markAllNotificationsRead'])->name('admin.notifications.markAllRead');
        Route::get('/api/notifications/unread-count', function () {
            return response()->json([
                'count' => Notification::where('is_read', false)->count()
            ]);
        })->name('api.notifications.unread_count');
    });

    /*
    |--------------------------------------------------------------------------
    | User Registration (Admin Only)
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/register-user', function () {
        if (Auth::user()->role !== 'Admin') {
            abort(403, 'غير مصرح لك بالوصول لهذه الصفحة');
        }
        return app(\App\Http\Controllers\Auth\RegisterController::class)->showRegistrationForm();
    })->name('admin.register.user');

    Route::post('/admin/register-user', function (\Illuminate\Http\Request $request) {
        if (Auth::user()->role !== 'Admin') {
            abort(403, 'غير مصرح لك بالوصول لهذه الصفحة');
        }
        return app(\App\Http\Controllers\Auth\RegisterController::class)->register($request);
    })->name('admin.register.user.post');
});
// مسارات مخطط الحالة المالية
Route::middleware(['auth'])->group(function () {
    Route::get('/reports/financial', [App\Http\Controllers\FinancialStatusController::class, 'index'])
        ->name('financial.status.index');
    Route::get('/financial/data', [App\Http\Controllers\FinancialStatusController::class, 'getFinancialData'])
        ->name('financial.status.data');
    Route::get('/financial/booking/{id}', [App\Http\Controllers\FinancialStatusController::class, 'getBookingFinancialDetails'])
        ->name('financial.status.booking');
    Route::get('/financial/tracking', [App\Http\Controllers\FinancialStatusController::class, 'getFinancialTrackingData'])
        ->name('financial.status.tracking');
    Route::get('/financial/tracking/export', [App\Http\Controllers\FinancialStatusController::class, 'exportFinancialTracking'])
        ->name('financial.status.tracking.export');
});
// شركة مصر : 
Route::middleware(['auth', \App\Http\Middleware\AdminOrEmployeeMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    // تقارير أرباح ومصروفات شركة مصر
    Route::get('/masr-financial-reports', [App\Http\Controllers\MasrFinancialReportController::class, 'index'])->name('masr.financial-reports.index');
    Route::get('/masr-financial-reports/create', [App\Http\Controllers\MasrFinancialReportController::class, 'create'])->name('masr.financial-reports.create');
    Route::post('/masr-financial-reports', [App\Http\Controllers\MasrFinancialReportController::class, 'store'])->name('masr.financial-reports.store');
    Route::get('/masr-financial-reports/{report}/edit', [App\Http\Controllers\MasrFinancialReportController::class, 'edit'])->name('masr.financial-reports.edit');
    Route::put('/masr-financial-reports/{report}', [App\Http\Controllers\MasrFinancialReportController::class, 'update'])->name('masr.financial-reports.update');
    Route::delete('/masr-financial-reports/{report}', [App\Http\Controllers\MasrFinancialReportController::class, 'destroy'])->name('masr.financial-reports.destroy');
    Route::get('/masr-financial-reports/filter', [App\Http\Controllers\MasrFinancialReportController::class, 'filter'])->name('masr.financial-reports.filter');
    Route::get('/masr-financial-reports/{report}', [App\Http\Controllers\MasrFinancialReportController::class, 'show'])->name('masr.financial-reports.show');
    Route::get('/bookings/list', [App\Http\Controllers\MasrFinancialReportController::class, 'list'])->name('bookings.list');
    Route::get('/bookings/{id}/info', [App\Http\Controllers\MasrFinancialReportController::class, 'info'])->name('bookings.info');
    Route::get('/operation-reports/get-booking-details', [App\Http\Controllers\MasrFinancialReportController::class, 'getBookingDetails'])->name('operation-reports.get-booking-details');
    // مصاريف مصر : 
    Route::resource('masr_expenses', \App\Http\Controllers\MasrExpenseController::class);
    Route::get('/masr-expenses/filter', [App\Http\Controllers\MasrExpenseController::class, 'filter'])->name('masr_expenses.filter');
});

/*
|--------------------------------------------------------------------------
| Land Trip Management Routes (Admin & Employee)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', \App\Http\Middleware\AdminOrEmployeeMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    // إدارة الرحلات وأنواعها
    Route::resource('land-trips', LandTripController::class);
    Route::resource('trip-types', TripTypeController::class)->except('show');

    // حجوزات الرحلات
    Route::get('land-trips/{land_trip}/bookings', [LandTripController::class, 'showBookings'])->name('land-trips.bookings');
    Route::get('land-trips/{land_trip}/create-booking', [LandTripController::class, 'createBooking'])->name('land-trips.create-booking');
    Route::post('land-trips/{land_trip}/store-booking', [LandTripController::class, 'storeBooking'])->name('land-trips.store-booking');
    Route::get('land-trips/bookings/{booking}/edit', [LandTripController::class, 'editBooking'])->name('land-trips.bookings.edit');
    Route::put('land-trips/bookings/{booking}/update', [LandTripController::class, 'updateBooking'])->name('land-trips.update-booking');
    Route::put('land-trips/bookings/{booking}/change-trip', [LandTripController::class, 'changeBookingTrip'])->name('land-trips.change-booking-trip');
    Route::get('land-trips/{landTrip}/room-types', [LandTripController::class, 'getTripRoomTypes'])->name('land-trips.room-types');
    Route::get('land-trips/bookings/{booking}/voucher', [CompanyLandTripController::class, 'downloadVoucher'])->name('land-trips.bookings.voucher');
    Route::delete('land-trips/bookings/{booking}', [LandTripController::class, 'destroyBooking'])->name('land-trips.bookings.destroy');


    // تقارير العمليات
    Route::prefix('operation-reports')->name('operation-reports.')->group(function () {
        Route::get('/', [BookingOperationReportController::class, 'index'])->name('index');
        Route::get('/create', [BookingOperationReportController::class, 'create'])->name('create');
        Route::get('/charts', [BookingOperationReportController::class, 'charts'])->name('charts');

        // API Routes (يجب أن تكون قبل المعاملات الديناميكية)
        Route::get('/get-booking-details', [BookingOperationReportController::class, 'getBookingDetails'])->name('get-booking-details');
        Route::get('/get-client-data', [BookingOperationReportController::class, 'getClientData'])->name('get-client-data');
        Route::get('/get-booking-data', [BookingOperationReportController::class, 'getBookingData'])->name('get-booking-data');
        Route::get('/api/clients/search', [BookingOperationReportController::class, 'searchClients'])->name('api.clients.search');
        Route::get('/api/client/latest-booking/{name}', [BookingOperationReportController::class, 'getClientLatestBooking'])->name('api.client.latest-booking');

        // CRUD Routes (المعاملات الديناميكية في النهاية)
        Route::post('/', [BookingOperationReportController::class, 'store'])->name('store');
        Route::get('/{operationReport}', [BookingOperationReportController::class, 'show'])->name('show');
        Route::get('/{operationReport}/edit', [BookingOperationReportController::class, 'edit'])->name('edit');
        Route::put('/{operationReport}', [BookingOperationReportController::class, 'update'])->name('update');
        Route::delete('/{operationReport}', [BookingOperationReportController::class, 'destroy'])->name('destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Employee User Management Routes (Admin & Employee)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', \App\Http\Middleware\AdminOrEmployeeMiddleware::class])->group(function () {
    Route::post('/admin/employees/create-user', [AdminController::class, 'createEmployeeUser'])->name('admin.createEmployeeUser');
    Route::post('/admin/employees/link-user', [AdminController::class, 'linkEmployeeUser'])->name('admin.linkEmployeeUser');
    Route::delete('/admin/employees/{employee}/unlink-user', [AdminController::class, 'unlinkEmployeeUser'])->name('admin.unlinkEmployeeUser');
});

/*
|--------------------------------------------------------------------------
| Company Land Trip Booking Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', \App\Http\Middleware\IsCompany::class])->prefix('company')->name('company.')->group(function () {
    // الرحلات البرية للشركات
    Route::get('land-trips', [CompanyLandTripController::class, 'index'])->name('land-trips.index');
    Route::get('land-trips/{landTrip}', [CompanyLandTripController::class, 'show'])->name('land-trips.show');
    Route::post('land-trips/{landTrip}/book', [CompanyLandTripController::class, 'book'])->name('land-trips.book');
    Route::get('my-bookings', [CompanyLandTripController::class, 'myBookings'])->name('land-trips.my-bookings');
    Route::get('land-trips/booking/{booking}/voucher', [CompanyLandTripController::class, 'voucher'])->name('land-trips.voucher');
    Route::get('land-trips/booking/{booking}/download-voucher', [CompanyLandTripController::class, 'downloadVoucher'])->name('land-trips.downloadVoucher');
});

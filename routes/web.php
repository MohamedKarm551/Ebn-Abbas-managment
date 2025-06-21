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

        return redirect()->intended('/bookings');
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

        // مخطط العلاقات
        Route::get('/network-graph', [ReportController::class, 'networkGraph'])->name('network.graph');
        Route::get('/network-data', [ReportController::class, 'getNetworkData'])->name('network.data');

        // المصاريف الشهرية
        Route::get('/admin/monthly-expenses', [App\Http\Controllers\MonthlyExpenseController::class, 'index'])->name('admin.monthly-expenses.index');
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
            Route::get('/{company}', [CompanyPaymentController::class, 'show'])->name('show');
            Route::get('/{company}/create', [CompanyPaymentController::class, 'create'])->name('create');
            Route::post('/{company}', [CompanyPaymentController::class, 'store'])->name('store');
            Route::get('/{company}/{payment}/edit', [CompanyPaymentController::class, 'edit'])->name('edit');
            Route::put('/{company}/{payment}', [CompanyPaymentController::class, 'update'])->name('update');
            Route::delete('/{company}/{payment}', [CompanyPaymentController::class, 'destroy'])->name('destroy');
            Route::post('/{company}/apply-discount', [CompanyPaymentController::class, 'applyDiscount'])->name('apply-discount');
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
    Route::get('land-trips/bookings/{booking}/voucher', [CompanyLandTripController::class, 'downloadVoucher'])->name('land-trips.bookings.voucher');

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
